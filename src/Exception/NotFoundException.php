<?php

namespace App\Exception;

use Doctrine\DBAL\Driver\OCI8\Exception\Error;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NotFoundException
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            throw new NotFoundHttpException('Not found.');;
        }
    }
}
