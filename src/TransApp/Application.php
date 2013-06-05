<?php

/**
 * This file is part of TransApp.
 *
 * (c) Lhassan Baazzi <baazzilhassan@gmail.com>
 */

namespace TransApp;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Silex\Application as BaseApplication;

class Application extends BaseApplication
{

    /**
     * __construct()
     *
     * @param  string $environment
     * @return void
     */
    public function __construct(Request $request, $environment)
    {
        // set the handler exception, convert an exception to a response
        set_exception_handler(array($this, 'handleException'));

        parent::__construct();

        $this['request'] = $request;

        $this['environment'] = strtolower((string) $environment);
        $this['debug'] = 'dev' === $this['environment'];

        $this->registerListenersToEvents();

        $this->registerServicesProviders();

        $this->configureServicesProviders();
    }

    /**
     * getRootDir()
     *
     * @return string path to the root dir
     */
    public function getRootDir()
    {
        return realpath(__DIR__.'/../..');
    }

    /**
     * getTwigViewsDir()
     *
     * @return string path to twig views dir
     */
    public function getTwigViewsDir()
    {
        return $this->getRootDir().'/app/resources/views';
    }

    /**
     * getTwigViewsDirCache()
     *
     * @return string path to twig views dir cache
     */
    public function getTwigViewsDirCache()
    {
        return $this->getRootDir().'/app/cache/views';
    }

    /**
     * getTwigViewsDir()
     *
     * @return string path to the translations catalogue dir
     */
    public function getTranslationsDir()
    {
        return $this->getRootDir().'/app/resources/translations';
    }

    /**
     * getTranslationsDirCache()
     *
     * @return string path to the translations catalogue dir cache
     */
    public function getTranslationsDirCache()
    {
        return $this->getRootDir().'/app/cache/translations';
    }

    /**
     * isDebug()
     *
     * @return bool
     */
    public function isDebug()
    {
        return $this['debug'];
    }

    /**
     * getEnvironment()
     *
     * @return string
     */
    public function getEnvironment()
    {
        return $this['environment'];
    }

    /**
     * handleException()
     *
     * @param  Exception $e
     * @return void
     */
    public function handleException(\Exception $e)
    {

        $event = new GetResponseForExceptionEvent(
            $this['kernel'],
            $this['request'],
            HttpKernelInterface::MASTER_REQUEST,
            $e
        );

        $this['dispatcher']->dispatch(KernelEvents::EXCEPTION, $event);

        if ($event->hasResponse()) {
            $response = $event->getResponse()->send();
            $this->terminate($this['request'], $response);

        } else {
            throw $e;
        }
    }

    /**
     * registerSubscribersToEvents()
     *
     * @return void
     */
    protected function registerListenersToEvents()
    {
        $this['dispatcher']->addSubscriber(new EventListener\LoadConfigurationListener($this));
        $this['dispatcher']->addSubscriber(new EventListener\LoadRoutesListener($this));
        $this['dispatcher']->addSubscriber(new EventListener\RenderTemplateListener($this));
    }

    /**
     * registerServicesProviders()
     *
     * @return void
     */
    protected function registerServicesProviders()
    {
        $this->register(new \TransApp\Provider\UrlGeneratorServiceProvider());

        $this->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path'     => $this->getTwigViewsDir(),
            'twig.options'  => array(
                'debug'     => $this->isDebug(),
                'cache'     => $this->getTwigViewsDirCache()
            ),
        ));

        $this->register(new \Silex\Provider\TranslationServiceProvider(), array(
            'locale_fallback'   => $this['locale'],
        ));
    }

    /**
     * configureServicesProviders()
     *
     * @return void
     */
    protected function configureServicesProviders()
    {
        $app = $this;

        // Auto reload for templates when a change is make
        $this['twig']->enableAutoReload();

        // add global vars to twig
        $this['twig'] = $this->share($this->extend('twig', function($twig, $app) {
            $twigGlobalVars = array();

            if (isset($app['config']) && isset($app['config']['twig']) && isset($app['config']['twig']['vars']))
                $twigGlobalVars = $app['config']['twig']['vars'];

            // globals vars are grouped under the "twigvars" key
            $twig->addGlobal('twigvars', $twigGlobalVars);

            $twig->addExtension(new Twig\Extension\AssetExtension($app));

            return $twig;
        }));

        // add translations resources
        $this['translator'] = $this->share($this->extend('translator', function($translator, $app) {
            $translator->addLoader('xliff', new XliffFileLoader());
            $translator->addLoader('yml', new YamlFileLoader());

            $finder = new Finder();

            // only xliff and yaml formats are accepted for translation
            $finder->files()->name('/\.(xliff|yml)$/')->in($app->getTranslationsDir());

            foreach ($finder as $file) {
                $fileBasenameExploded = explode('.', $file->getFileName());

                if (count($fileBasenameExploded) < 3) {
                    throw new \RuntimeException(
                        sprintf(
                            'The file "%s" is not correctly rename, must be "domain.locale.format" (e.g. messages.en.xliff)',
                            $file->getFileName()
                        )
                    );
                }

                $translator->addResource(
                    $fileBasenameExploded[2], $file,
                    $fileBasenameExploded[1], $fileBasenameExploded[0]
                );
            }

            return $translator;
        }));
    }
}
