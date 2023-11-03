<?php

namespace App\MessageHandler;

use App\Message\NewAlcoholMessage;
use App\Service\EmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class NewAlcoholMessageHandler
{
    public function __construct(
        private EmailService $emailService
    ) {
    }

        public function __invoke(NewAlcoholMessage $message)
        {
            $subject = $message->getSubject();
            $recipient = $message->getRecipient();
            $context = $message->getContext();

            $this->emailService->sendEmail($subject, $recipient, $context);
        }
}
