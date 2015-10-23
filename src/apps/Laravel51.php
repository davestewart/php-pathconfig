<?php

namespace pathconfig\apps;

use Illuminate\Foundation\Application;
use pathconfig\PathConfig;

/**
 * Replacement Application class for Laravel 5.1 apps
 *
 * @package davestewart\pathconfig
 */
class Laravel51 extends Laravel50
{

    // -----------------------------------------------------------------------------------------------------------------
    // cached getters

        /**
         * Get the path to the configuration cache file.
         *
         * @return string
         */
        public function getCachedConfigPath()
        {
            return $this->paths->get('bootstrap', 'cache/config.php');
        }

        /**
         * Get the path to the routes cache file.
         *
         * @return string
         */
        public function getCachedRoutesPath()
        {
            return $this->paths->get('bootstrap', 'cache/routes.php');
        }

        /**
         * Get the path to the cached "compiled.php" file.
         *
         * @return string
         */
        public function getCachedCompilePath()
        {
            return $this->paths->get('bootstrap', 'cache/compiled.php');
        }

        /**
         * Get the path to the cached services.json file.
         *
         * @return string
         */
        public function getCachedServicesPath()
        {
            return $this->paths->get('bootstrap', 'cache/services.php');
        }

}
