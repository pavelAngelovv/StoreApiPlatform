<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class NewItemSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoggerInterface $logger,
        private UserRepository $userRepository,
        private EmailService $emailService
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
                $this->emailService->sendEmail($subject, $adminUser->getEmail(), $context);
                $this->logger->info('Email message sent to: ' . $adminUser->getEmail());
            }
        }
    }
}
