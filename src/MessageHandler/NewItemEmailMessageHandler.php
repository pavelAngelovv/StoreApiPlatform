<?php

namespace App\MessageHandler;

use App\Message\NewItemEmailMessage;
use App\Service\EmailService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class NewItemEmailMessageHandler
{
    public function __construct(
        private EmailService $emailService
    ) {
    }

        public function __invoke(NewItemEmailMessage $message)
        {
            $subject = $message->getSubject();
            $recipient = $message->getRecipient();
            $context = $message->getContext();

            $this->emailService->sendEmail($subject, $recipient, $context);
        }
}
