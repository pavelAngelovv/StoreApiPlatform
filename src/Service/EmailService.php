<?php

namespace App\Service;

use App\Message\NewItemMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private MessageBusInterface $bus
    ) {
    }

    public function sendEmail( string $subject, string $recipient, array $context)
    {
                $message = new NewItemMessage(
                    $subject,
                    $recipient,
                    $context
                );

                $this->bus->dispatch($message);
    }
}
