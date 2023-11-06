<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    public function sendEmail(string $subject, string $recipient, array $context): void
    {
        $email = (new TemplatedEmail())
            ->from('3c1016dcc8657a@inbox.mailtrap.io')
            ->subject($subject)
            ->to($recipient)
            ->htmlTemplate('new.alcohol.email.html.twig')
            ->context($context);

        $this->mailer->send($email);
    }
}
