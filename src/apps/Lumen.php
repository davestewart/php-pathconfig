<?php

namespace pathconfig\apps;

/**
 * Class LumenApp
 * @package davestewart\pathconfig
 */
class Lumen extends \Laravel\Lumen\Application
{
    /**
     * @var PathConfig
     */
    protected $paths;

    /**
     * Override Lumen Application constructor and set config, resource and storage paths
     */
    public function __construct()
    {
        // initialise paths
        $this->paths            = \pathconfig\PathConfig::instance()->load();

        // construct
        parent::__construct(path());

        // update paths
        $this->configPath       = path('config');
        $this->resourcePath     = path('resources');
        $this->storagePath      = path('storage');
    }

    /**
      * Override hardcoded database path
      * @return string
      */
    public function databasePath()
    {
        return realpath(path('database'));
    }

    /**
      * Override hardcoded language path
      * @return string
      */
    protected function getLanguagePath()
    {
        return path('lang');
    }

}
