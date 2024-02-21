<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\Response;

class KernelException
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            
            $response = new Response('Page not found!', Response::HTTP_NOT_FOUND);

            $event->setResponse($response);
        }
    }
}