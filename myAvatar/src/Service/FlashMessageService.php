<?php

namespace App\Service;

use \Psr\Container\ContainerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

class FlashMessageService implements FlashMessageServiceInterface {

    public function __construct(private RequestStack $requestStack) {
    }

    public function getFlashbag(): mixed {
        $session = $this->requestStack->getSession();
        if (!$session instanceof FlashBagAwareSessionInterface) {
            throw new SessionNotFoundException();
        }
        return $session->getFlashBag();
    }

    public function addErrorsForm(FormInterface $form): void {
        $errors = $form->getErrors(true);
        $flashBag = $this->getFlashbag();
        foreach ($errors as $error) {
            $flashBag->add(FlashMessageType::ERROR->value, $error->getMessage());
        }
    }

    public function add(FlashMessageType $type, string $message): void {
        $flashBag = $this->getFlashbag();
        $flashBag->add($type->value, $message);
    }
}
