<?php

namespace App\Message;

final class NewItemMessage
{
    public function __construct(
        private string $subject,
        private string $recipient,
        private array $context
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getRecipient(): string
    {
        return $this->recipient;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
