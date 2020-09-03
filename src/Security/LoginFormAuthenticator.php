<?php
declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $router;
    private $csrf_token_manager;
    private $password_encoder;

    public function __construct(
        RouterInterface $router,
        CsrfTokenManagerInterface $csrf_token_manager,
        UserPasswordEncoderInterface $password_encoder
    ) {
        $this->router = $router;
        $this->csrf_token_manager = $csrf_token_manager;
        $this->password_encoder = $password_encoder;
    }

    public function supports(Request $request): bool
    {
        return 'app.login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request): array
    {
        $login = $request->request->get('login');

        $credentials = [
            'email' => $login['email'] ?? null,
            'password' => $login['password'] ?? null,
            'csrf_token' => $login['_token'] ?? null,
        ];
        $request->getSession()->set(Security::LAST_USERNAME, $credentials['email']);

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $user_provider): ?UserInterface
    {
        $token = new CsrfToken('login', $credentials['csrf_token']);
        if (!$this->csrf_token_manager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        return $user_provider->loadUserByUsername($credentials['email']);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->password_encoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $provider_key): Response
    {
        if ($target_path = $this->getTargetPath($request->getSession(), $provider_key)) {
            return new RedirectResponse($target_path);
        }

        return new RedirectResponse($this->router->generate('app.index'));
    }

    protected function getLoginUrl(): string
    {
        return $this->router->generate('app.login');
    }
}
