<?php

/**
 * This file is part of TransApp.
 *
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 */

namespace TransApp\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use TransApp\Application;

class RenderTemplateListener implements EventSubscriberInterface
{

    protected $app;

    /**
     * __construct()
     *
     * @param  Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * renderTemplate()
     *
     * @param  GetResponseEvent $event
     * @return void
     */
    public function renderTemplate(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $this->setRouteNameInRequest($request);
        $this->setLocaleForTranslator();

        if (!$event->hasResponse()) {
            $event->setResponse(new Response(
                $this->app['twig']->render($request->get('_template'))
            ));
        }
    }

    /**
     * getSubscribedEvents()
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('renderTemplate', 0),
        );
    }

    /**
     * setRouteNameInRequest()
     *
     * @param  Request $request
     * @return void
     */
    protected function setRouteNameInRequest(Request $request)
    {
        if (false !== strpos($request->get('_route'), LoadRoutesListener::ROUTING_PREFIX)) {
            $request->attributes->set(
                '_route',
                substr($request->get('_route'), strlen(LoadRoutesListener::ROUTING_PREFIX))
            );
        }
    }

    /**
     * setLocaleForTranslator()
     *
     * @return void
     */
    protected function setLocaleForTranslator()
    {
        $this->app['translator']->setLocale($this->app['locale']);
    }
}
