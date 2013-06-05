<?php

/**
 * This file is part of TransApp. 
 * 
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 */

namespace TransApp\Twig\Extension;

use TransApp\Application;

class AssetExtension extends \Twig_Extension 
{

    private $app;

    /**
     * __construct()
     * 
     * @param Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * getFunctions()
     * 
     * @return void
     */
    public function getFunctions()
    {
        return array(
            'asset' => new \Twig_Function_Method($this, 'asset'),
        );
    }

    /**
     * asset()
     * 
     * @param string $url
     * @return string
     */
    public function asset($url) 
    {
        return sprintf('%s/%s', $this->app['request']->getBasePath(), ltrim($url, '/'));
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'transapp.asset';
    }
}
