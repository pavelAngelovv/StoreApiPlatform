<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordSubscriber implements EventSubscriber
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $this->hashPassword($args);
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->hashPassword($args);
    }

    private function hashPassword(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof User) {
            if ($plainPassword = $entity->getPlainPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword($entity, $plainPassword);
                $entity->setPassword($hashedPassword);
            }
        }
    }
}
