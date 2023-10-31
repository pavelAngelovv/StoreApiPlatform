<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Event\NewItemEvent;
use App\Repository\UserRepository; 
use Psr\Log\LoggerInterface; 
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class NewItemSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private LoggerInterface $logger ,
        private MailerInterface $mailer,
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
        $result = $event->getControllerResult();
        $request = $event->getRequest();

        if ( $request->isMethod('POST')) {
            $this->logger->info('New Alcohol event is being dispatched.');
       
            $newAlcoholEvent = new NewItemEvent($result);
            $this->eventDispatcher->dispatch($newAlcoholEvent);
            
            $adminUsers = $this->userRepository->findByRole('ROLE_ADMIN');

            foreach ($adminUsers as $adminUser) {
                $email = (new Email())
                    ->from('4d572b37425429@inbox.mailtrap.io')
                    ->to($adminUser->getEmail())
                    ->subject('New Alcohol Created')
                    ->html('<p>A new alcohol was created.</p>');

                $this->mailer->send($email);
                $this->logger->info('Email sent successfully to: ' . $adminUser->getEmail());
            }
        }
    }
}
