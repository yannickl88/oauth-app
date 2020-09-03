<?php
declare(strict_types=1);

namespace App\Controller;

use App\Form\ApproveScopesType;
use App\Form\LoginType;
use App\Form\RegisterType;
use App\Orm\Entity\Authentication;
use App\Orm\Entity\User;
use App\Orm\Repository\UserRepository;
use App\Struct\NewUserStruct;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\RequestTypes\AuthorizationRequest;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController
{
    private $form_factory;
    private $token_storage;
    private $authorization_server;
    private $psr_http_factory;
    private $response_factory;
    private $user_repository;
    private $entity_manager;
    private $router;
    private $flash_bag;

    public function __construct(
        FormFactoryInterface $form_factory,
        TokenStorageInterface $token_storage,
        AuthorizationServer $authorization_server,
        HttpMessageFactoryInterface $psr_http_factory,
        ResponseFactoryInterface $response_factory,
        UserRepository $user_repository,
        EntityManagerInterface $entity_manager,
        RouterInterface $router,
        FlashBagInterface $flash_bag
    )
    {
        $this->form_factory = $form_factory;
        $this->token_storage = $token_storage;
        $this->authorization_server = $authorization_server;
        $this->psr_http_factory = $psr_http_factory;
        $this->response_factory = $response_factory;
        $this->user_repository = $user_repository;
        $this->entity_manager = $entity_manager;
        $this->router = $router;
        $this->flash_bag = $flash_bag;
    }

    /**
     * @Route("/login/", name="app.login")
     * @Template("login.html.twig")
     */
    public function loginAction(Request $request, AuthenticationUtils $authentication_utils)
    {
        if ($this->token_storage->getToken()->getUser() instanceof UserInterface) {
            return new RedirectResponse($this->router->generate('app.index'));
        }

        $form = $this->form_factory->create(LoginType::class, [
            'email' => $authentication_utils->getLastUsername(),
        ]);

        return [
            'form' => $form->createView(),
            'redirect_to' => $request->query->get('redirect_to'),
            'error' => $authentication_utils->getLastAuthenticationError(),
        ];
    }

    /**
     * @Route("/oauth/scopes/", name="app.login.scopes")
     * @Template("scopes.html.twig")
     */
    public function approveScopesAction(Request $request)
    {
        if (!($this->token_storage->getToken()->getUser() instanceof UserInterface)) {
            return new RedirectResponse($this->router->generate('app.login'));
        }

        $auth_request = $request->getSession()->get('_security.oauth.auth_request');

        if (!($auth_request instanceof AuthorizationRequest)) {
            throw new \LogicException("need auth request");
        }

        $form = $this->form_factory->create(ApproveScopesType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            if (null === ($user = $this->user_repository->findOneByEmail($auth_request->getUser()->getIdentifier()))) {
                throw new \UnexpectedValueException('Expected use, got NULL');
            }
            $user->getAuthentication()->approveScopes($auth_request->getScopes());

            $this->entity_manager->persist($user);
            $this->entity_manager->flush();

            return new RedirectResponse($this->router->generate("app.api.auth.authorize_finalize"));
        }

        return [
            'form' => $form->createView(),
            'auth_request' => $auth_request,
        ];
    }

    /**
     * @Route("/oauth/authorize", name="app.api.auth.authorize")
     */
    public function authorizeAction(Request $request)
    {
        $response = $this->response_factory->createResponse();

        try {
            $auth_request = $this->authorization_server->validateAuthorizationRequest(
                $this->psr_http_factory->createRequest($request)
            );

            $request->getSession()->set('_security.oauth.auth_request', $auth_request);

            return new RedirectResponse($this->router->generate("app.api.auth.authorize_finalize"));
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    /**
     * @Route("/oauth/authorize/approve", name="app.api.auth.authorize_finalize")
     */
    public function authorizeFinalizeAction(Request $request)
    {
        $token = $this->token_storage->getToken();

        if (!$token->getUser() instanceof UserInterface) {
            throw new AuthenticationException("No user found");
        }

        $response = $this->response_factory->createResponse();

        try {
            $auth_request = $request->getSession()->get('_security.oauth.auth_request');

            if (!($auth_request instanceof AuthorizationRequest)) {
                return new RedirectResponse($this->router->generate('app.index'));
            }

            $user = $this->user_repository->findOneByEmail(
                $token->getUsername()
            );
            $auth_request->setUser(\App\Security\Oauth\User::fromEntity($user));

            if (!$user->getAuthentication()->hasApproved($auth_request->getScopes())) {
                return new RedirectResponse($this->router->generate("app.login.scopes"));
            }

            $auth_request->setAuthorizationApproved(true);

            return $this->authorization_server->completeAuthorizationRequest($auth_request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    /**
     * @Route("/oauth/access_token", name="app.api.auth.access_token")
     */
    public function accessTokenAction(ServerRequestInterface $request, ResponseFactoryInterface $response_factory): ResponseInterface
    {
        $response = $response_factory->createResponse();

        try {
            return $this->authorization_server->respondToAccessTokenRequest($request, $response);
        } catch (OAuthServerException $exception) {
            return $exception->generateHttpResponse($response);
        }
    }

    /**
     * @Route("/signup/", name="app.signup")
     * @Template("signup.html.twig")
     */
    public function signUpAction(Request $request)
    {
        $form = $this->form_factory->create(RegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var NewUserStruct $data */
            $data = $form->getData();

            $user = new User($data->email, new Authentication($data->password));

            $this->entity_manager->persist($user);
            $this->entity_manager->flush();

            $this->flash_bag->add('success', 'You are now signed up!');

            return new RedirectResponse($this->router->generate("app.api.auth.authorize_finalize"));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/logout/", name="app.logout", methods={"GET"})
     */
    public function logoutAction(): void
    {
    }
}
