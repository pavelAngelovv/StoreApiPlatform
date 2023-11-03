<?php

namespace App\EventSubscriber;

use App\Entity\Alcohol;
use App\Message\NewAlcoholMessage;
use App\Repository\UserRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Messenger\MessageBusInterface;

class NewAlcoholSubscriber implements EventSubscriber
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private UserRepository $userRepository
    ) {
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof Alcohol) {
            $adminUsers = $this->userRepository->findByRole('ROLE_ADMIN');

            foreach ($adminUsers as $adminUser) {
                $message = new NewAlcoholMessage(
                    'New Alcohol',
                    $adminUser->getEmail(),
                    [
                        'title' => $entity->getName(),
                        'content' => 'This the newest ' . $entity->getType() . ' on the market'
                    ]
                );

                $this->messageBus->dispatch($message);
            }
        }
    }

    public function getSubscribedEvents()
    {
        return ['prePersist'];
    }
}
