<?php

namespace App\MessageHandler;

use App\Message\NewItemMessage;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

#[AsMessageHandler]
final class NewItemMessageHandler
{
    public function __construct(
        private MailerInterface $mailer
    ) {
    }

    public function __invoke(NewItemMessage $message)
    {
        $email = (new TemplatedEmail())
            ->from('4d572b37425429@inbox.mailtrap.io')
            ->subject($message->getSubject())
            ->to($message->getRecipient())
            ->htmlTemplate('new.item.email.html.twig')
            ->context($message->getContext());

        $this->mailer->send($email);
    }
}
