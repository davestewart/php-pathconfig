<?php

namespace pathconfig\apps;

use Illuminate\Foundation\Application;
use pathconfig\PathConfig;

/**
 * Replacement Application class for Laravel 5.0 apps
 *
 * @package davestewart\pathconfig
 */
class Laravel50 extends Application
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
         * Override Laravel Application constructor
         *
         * Parent constructor will call bindPathsInContainer() to set config, database, lang, public, and storage paths
         *
         */
        public function __construct()
        {
            // load paths
            $this->paths = PathConfig::instance()->load();

            // parent constructor manages all path setup
            parent::__construct($this->paths->get());
        }

    // -----------------------------------------------------------------------------------------------------------------
    // setters

        /**
         * Set the base path for the application.
         *
         * @param string $basePath
         * @return $this
         */
        public function setBasePath($basePath)
        {
            $this->paths->set('base', $basePath);
            return parent::setBasePath($basePath);
        }

        /**
         * Set the database directory.
         *
         * @param  string $path
         * @return $this
         * @throws \Exception
         */
        public function useDatabasePath($path)
        {
            throw new \Exception(__FUNCTION__.' is not implemented. Use the `paths.php` configuration file instead.');
        }

        /**
         * Set the storage directory.
         *
         * @param  string $path
         * @return $this
         * @throws \Exception
         */
        public function useStoragePath($path)
        {
            throw new \Exception(__FUNCTION__.' is not implemented. Use the `paths.php` configuration file instead.');
        }


    // -----------------------------------------------------------------------------------------------------------------
    // getters

        /**
         * Get the path to the application "app" directory.
         *
         * @return string
         */
        public function path()
        {
            return $this->paths->get('app');
        }

        /**
         * Get the base path of the Laravel installation.
         *
         * @return string
         */
        public function basePath()
        {
            return $this->basePath;
        }

        /**
         * Get the path to the application configuration files.
         *
         * @return string
         */
        public function configPath()
        {
            return $this->paths->get('config');
        }

        /**
         * Get the path to the database directory.
         *
         * @return string
         */
        public function databasePath()
        {
            return $this->paths->get('database');
        }

        /**
         * Get the path to the language files.
         *
         * @return string
         */
        public function langPath()
        {
            return $this->paths->get('lang');
        }

        /**
         * Get the path to the public / web directory.
         *
         * @return string
         */
        public function publicPath()
        {
            return $this->paths->get('public');
        }

        /**
         * Get the path to the storage directory.
         *
         * @return string
         */
        public function storagePath()
        {
            return $this->paths->get('storage');
        }


    // -----------------------------------------------------------------------------------------------------------------
    // cached getters

        /**
         * Get the path to the configuration cache file.
         *
         * @return string
         */
        public function getCachedConfigPath()
        {
            return $this->vendorIsWritableForOptimizations()
                ? $this->paths->get('vendor/config.php')
                : $this->paths->get('storage', 'framework/config.php');
        }

        /**
         * Get the path to the routes cache file.
         *
         * @return string
         */
        public function getCachedRoutesPath()
        {
            return $this->vendorIsWritableForOptimizations()
                ? $this->paths->get('vendor/routes.php')
                : $this->paths->get('storage', 'framework/routes.php');
        }

        /**
         * Get the path to the cached "compiled.php" file.
         *
         * @return string
         */
        public function getCachedCompilePath()
        {
            return $this->vendorIsWritableForOptimizations()
                ? $this->paths->get('vendor/compiled.php')
                : $this->paths->get('storage', 'framework/compiled.php');
        }

        /**
         * Get the path to the cached services.json file.
         *
         * @return string
         */
        public function getCachedServicesPath()
        {
            return $this->vendorIsWritableForOptimizations()
                ? $this->paths->get('vendor/services.json')
                : $this->paths->get('storage', 'framework/services.json');
        }

}
