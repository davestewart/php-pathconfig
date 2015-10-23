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
        parent::__construct($this->paths->get());

        // update paths
        $this->configPath       = $this->paths->get('config');
        $this->resourcePath     = $this->paths->get('resources');
        $this->storagePath      = $this->paths->get('storage');
    }

    /**
      * Override hardcoded database path
      * @return string
      */
    public function databasePath()
    {
        return $this->paths->get('database');
    }

    /**
      * Override hardcoded language path
      * @return string
      */
    protected function getLanguagePath()
    {
        return $this->paths->get('lang');
    }

}
