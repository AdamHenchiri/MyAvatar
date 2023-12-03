<?php

namespace App\Service;

use App\Entity\Festival;
use App\Entity\Utilisateur;
use App\Service\FlashMessageService as ServiceFlashMessageService;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

enum FlashMessageType: string {
    case SUCCESS = 'success';
    case ERROR = 'error';
    case WARNING = 'warning';
    case INFO = 'info';
}

class ErrorService {

    public function __construct(private ServiceFlashMessageService $flashMessageService, private RouterInterface $router) {
    }

    public function redirectToRoute(string $route): RedirectResponse {
        return new RedirectResponse($this->router->generate($route));
    }

    public function MustBeLoggedError(): RedirectResponse {
        $this->flashMessageService->add(FlashMessageType::ERROR, "Vous devez être connecté pour accéder à cette page");
        return $this->redirectToRoute('app_auth_login');
    }
}
