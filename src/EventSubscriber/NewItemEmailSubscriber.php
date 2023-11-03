<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Message\NewItemEmailMessage;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\MessageBusInterface;

class NewItemEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
        private UserRepository $userRepository
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['onKernelView', EventPriorities::POST_WRITE],
        ];
    }

    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();

        if ($request->isMethod('POST')) {
            $adminUsers = $this->userRepository->findByRole('ROLE_ADMIN');

            $subject = 'New Alcohol';
            $context = [
                'title' => 'A new alcohol was created!',
                'content' => 'A new item was created, check it out!'
            ];

            foreach ($adminUsers as $adminUser) {
                $message = new NewItemEmailMessage(
                    $subject,
                    $adminUser->getEmail(),
                    $context
                );

                $this->messageBus->dispatch($message);
            }
        }
    }
}
