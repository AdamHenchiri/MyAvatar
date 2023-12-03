<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthenticationSubscriber {
    public function __construct(private RequestStack $requestStack) {
    }

    public function getFlashbag(): mixed {
        return $this->requestStack->getSession()->getFlashBag();
    }

    #[AsEventListener]
    public function onAuthenticationSuccess(LoginSuccessEvent $event): void {
        $this->getFlashBag()->add("success", "Vous êtes maintenant connecté.");
    }

    #[AsEventListener]
    public function onAuthenticationFailure(LoginFailureEvent $event): void {
        $this->getFlashBag()->add("error", "Identifiants incorrects.");
    }

    #[AsEventListener]
    public function onLogoutSuccess(LogoutEvent $event): void {
        $this->getFlashBag()->add("success", "Vous êtes maintenant déconnecté.");
    }
}
