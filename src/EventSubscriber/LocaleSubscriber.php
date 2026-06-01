<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $defaultLocale = 'fr') {}

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        // Cookie takes priority (survives logout), then session, then default
        $locale = $request->cookies->get('_locale')
            ?? ($request->hasPreviousSession() ? $request->getSession()->get('_locale') : null)
            ?? $this->defaultLocale;

        $request->setLocale($locale);

        // Keep session in sync when a session exists
        if ($request->hasPreviousSession()) {
            $request->getSession()->set('_locale', $locale);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => [['onKernelRequest', 20]]];
    }
}
