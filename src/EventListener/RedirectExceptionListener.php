<?php

namespace TotalCRM\AmoCRM\EventListener;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use TotalCRM\AmoCRM\Exception\RedirectException;

/**
 * Class RedirectExceptionListener
 * @package TotalCRM\AmoCRM\EventListener
 */
class RedirectExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event instanceof RedirectException) {
            $event->setResponse($event->getException()->getRedirectResponse());
        }
    }
}