<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class MailerService
{
    private MailerInterface $mailerInterface;
 public function __construct(MailerInterface $mailerInterface){
     $this->mailerInterface = $mailerInterface;
 }

 public function send(string $to, string $subject, string $templateTwig, array $context): void{
    $email = (new TemplatedEmail())
        ->from(new Address('monsite@dev.fr', 'MonSiteDev'))
        ->to($to)
        ->subject($subject)
        ->htmlTemplate("mails/$templateTwig")
        ->context($context);

     $this->mailerInterface->send($email);

 }
}