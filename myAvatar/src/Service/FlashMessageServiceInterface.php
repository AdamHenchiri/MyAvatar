<?php

namespace App\Service;

use Symfony\Component\Form\FormInterface;

interface FlashMessageServiceInterface
{
    public function addErrorsForm(FormInterface $form):void;
}