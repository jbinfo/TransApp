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
use Igorw\Silex\ConfigServiceProvider;
use TransApp\Application;

class LoadConfigurationListener implements EventSubscriberInterface
{

    protected $app;

    /**
     * __construct()
     * 
     * @param   Application $app
     * @return  void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * loadConfiguration()
     * 
     * @param  GetResponseEvent $event
     * @return void
     */
    public function loadConfiguration(GetResponseEvent $event)
    {
        $file = $this->app->getRootDir().'/app/resources/config/'.$this->app->getEnvironment().'.json';

        if (file_exists($file)) {
            $this->app->register(new ConfigServiceProvider($file));
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
            KernelEvents::REQUEST => array('loadConfiguration', 100),
        );
    }
}
