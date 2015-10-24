<?php

namespace pathconfig\apps;

use pathconfig\PathConfig;

/**
 * Replacement Application class for Lumen 5.0 apps
 *
 * @package davestewart\pathconfig
 */
class Lumen50 extends \Laravel\Lumen\Application
{

    // -----------------------------------------------------------------------------------------------------------------
    // properties

        /**
         * @var PathConfig
         */
        protected $paths;


    // -----------------------------------------------------------------------------------------------------------------
    // instantiation

        /**
         * Override Lumen Application constructor and set config, resource and storage paths
         */
        public function __construct()
        {
            // initialise paths
            $this->paths            = PathConfig::instance()->load();

            // construct
            parent::__construct($this->paths->get());

            // update paths
            $this->resourcePath     = $this->paths->get('resources');
            $this->storagePath      = $this->paths->get('storage');

            // add config folder only if supplied
            if($config = $this->paths->get('config'))
            {
                $this->configPath = $config;
            }
        }


    // -----------------------------------------------------------------------------------------------------------------
    // getters

        /**
         * Get any path via the PathConfig object
         *
         * @param string $key
         * @param string $filepath
         * @return string
         */
        public function getPath($key = '', $filepath = '')
        {
            return $this->paths->get($key, $filepath);
        }

        /**
          * Override hardcoded database path
         *
          * @return string
          */
        public function databasePath()
        {
            return $this->paths->get('database');
        }

        /**
          * Override hardcoded language path
         *
          * @return string
          */
        protected function getLanguagePath()
        {
            return $this->paths->get('lang');
        }

}
