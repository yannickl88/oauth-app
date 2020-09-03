<?php
declare(strict_types=1);

namespace App\Security\Oauth;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class ExceptionListener implements EventSubscriberInterface
{
    private $token_storage;

    public function __construct(TokenStorageInterface $token_storage)
    {
        $this->token_storage = $token_storage;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof HttpException)) {
            return;
        }

        $token = $this->token_storage->getToken();

        if (!($token instanceof PostAuthenticationGuardToken) || $token->getProviderKey() !== 'api') {
            return;
        }

        $event->setResponse(new JsonResponse(["error" => $exception->getMessage()], $exception->getStatusCode()));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException']
        ];
    }
}