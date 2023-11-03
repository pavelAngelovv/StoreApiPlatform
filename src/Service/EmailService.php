<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private MessageBusInterface $bus
    ) {
    }

    public function sendEmail(string $subject, string $recipient, array $context)
    {
        $email = (new TemplatedEmail())
            ->from('4d572b37425429@inbox.mailtrap.io')
            ->subject($subject)
            ->to($recipient)
            ->htmlTemplate('new.item.email.html.twig')
            ->context($context);

        $this->mailer->send($email);
    }
}
