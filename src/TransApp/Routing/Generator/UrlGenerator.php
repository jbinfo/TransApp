<?php

/**
 * This file is part of TransApp.
 *
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 */

namespace TransApp\Routing\Generator;

use Symfony\Component\Routing\Generator\UrlGenerator as BaseUrlGenerator;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Psr\Log\LoggerInterface;
use TransApp\EventListener\LoadRoutesListener;
use TransApp\Application;

class UrlGenerator extends BaseUrlGenerator
{

    protected $app;

    /**
     * Constructor.
     *
     * @param Application          $app     The Application instance
     * @param RouteCollection      $routes  A RouteCollection instance
     * @param RequestContext       $context The context
     * @param LoggerInterface|null $logger  A logger instance
     */
    public function __construct(Application $app, RouteCollection $routes, RequestContext $context, LoggerInterface $logger = null)
    {
        $this->app = $app;
        $this->routes = $routes;
        $this->context = $context;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if ($this->app['default_locale'] !== $this->app['request']->getLocale()) {
            $name = LoadRoutesListener::ROUTING_PREFIX . $name;
        }

        if (null === $route = $this->routes->get($name)) {
            throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
        }

        // the Route has a cache of its own and is not recompiled as long as it does not get modified
        $compiledRoute = $route->compile();

        return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $name, $referenceType, $compiledRoute->getHostTokens());
    }
}
