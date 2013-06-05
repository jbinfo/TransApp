<?php

/**
 * This file is part of TransApp. 
 * 
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 */

namespace TransApp\Provider;

use Silex\ServiceProviderInterface;
use Silex\Application;
use TransApp\Routing\Generator\UrlGenerator;


class UrlGeneratorServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['url_generator'] = $app->share(function ($app) {
            $app->flush();

            return new UrlGenerator($app, $app['routes'], $app['request_context']);
        });
    }

    public function boot(Application $app)
    {
    }
}
