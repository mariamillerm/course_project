<?php

namespace AppBundle\Event;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocaleListener
{
    /**
     * Get locale from cookie
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $locale = $request->cookies->get('locale');

        if ($locale !== null) {
            $request->setLocale($locale);
        }
    }
}