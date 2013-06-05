<?php

/**
 * This file is part of TransApp.
 *
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 */

namespace TransApp\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use TransApp\Application;

class LoadRoutesListener implements EventSubscriberInterface
{

    const ROUTING_PREFIX = '__TRANS_APP_LOCALE__';

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
     * loadRoutes()
     *
     * @param  GetResponseEvent $event
     * @return void
     */
    public function loadRoutes(GetResponseEvent $event)
    {
        if (!empty($this->app['config']) && !empty($this->app['config']['routing'])) {
            $config = $this->app['config'];
            $routing = $config['routing'];

            if (!empty($routing['default_locale']))
                $this->app['locale'] = $routing['default_locale'];

            $this->app['default_locale'] = $this->app['locale'];
            $this->app['locales'] = array($this->app['default_locale']);

            if (!empty($routing['locales'])) {
                if (!is_array($routing['locales'])) {
                    throw new \InvalidArgumentException('parameter "locales" must be an array');
                }

                $this->app['locales'] = $routing['locales'];
            }

            $requirements = array('_method' => 'GET|POST');
            if (!empty($this->app['locales'])) {
                $requirements = array_merge($requirements, array('_locale' => implode('|', $this->app['locales'])));
            }

            if (!empty($routing['routes'])) {

                $routesByLocales = new RouteCollection();

                foreach ($routing['routes'] as $routeName => $itemRoute) {
                    if (!isset($itemRoute['paths']) || !is_array($itemRoute['paths'])) {
                        throw new \InvalidArgumentException(
                            sprintf('parameter "patterns" is not set or is not an array in this route "%s"', $routeName)
                        );
                    }

                    if (empty($itemRoute['template'])) {
                        throw new \InvalidArgumentException(
                            sprintf('a template name must be set for this route "%s"', $routeName)
                        );
                    }

                    $defaultsBase = array(
                        '_template' => $itemRoute['template'],
                        '_locale' => $this->app['default_locale']
                    );

                    $defaults = array(
                        '_template' => $itemRoute['template']
                    );

                    foreach ($itemRoute['paths'] as $pathName => $path) {

                        $this->app['routes']->add(
                            $pathName,
                            new Route(('/' === $path ? '/{_locale}' : $path), $defaultsBase, $requirements)
                        );

                        $routesByLocales->add(
                            sprintf('%s%s', static::ROUTING_PREFIX, $pathName),
                            new Route($path, $defaults, $requirements)
                        );
                    }
                }

                $routesByLocales->addPrefix('/{_locale}/');

                $this->app['routes']->addCollection($routesByLocales);

                unset($config['routing']['routes']);

                $this->app->offsetSet('config', $config);
            }
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
            KernelEvents::REQUEST => array('loadRoutes', 99),
        );
    }
}
