<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class OauthAuthenticator extends AbstractGuardAuthenticator
{
    private $access_token_repository;

    public function __construct(AccessTokenRepository $access_token_repository)
    {
        $this->access_token_repository = $access_token_repository;
    }

    public function start(Request $request, AuthenticationException $auth_exception = null)
    {
        return new JsonResponse(["error" => 'Oauth token is missing or invalid'], Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request)
    {
        return $request->headers->has('AUTHORIZATION');
    }

    public function getCredentials(Request $request)
    {
        $auth = $request->headers->get('AUTHORIZATION');

        if (preg_match('/^Bearer (.*)$/', $auth, $matches) !== 1) {
            throw new \UnexpectedValueException('Expected Bearer token.');
        }

        return [
            'token' => $matches[1],
        ];
    }

    public function getUser($credentials, UserProviderInterface $user_provider)
    {
        if (null === ($token = $this->access_token_repository->findByIdentifier($credentials['token']))) {
            return null;
        }

        if (null === ($user = $user_provider->loadUserByUsername($token->getUserIdentifier()))) {
            return null;
        }

        return new ScopedAppUser($user, $token->getScopes());
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response('', Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $provider_key)
    {
        return null;
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
