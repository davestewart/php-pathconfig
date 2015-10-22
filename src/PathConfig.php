<?php

namespace pathconfig
{

    /**
     * PathManager class
     *
     * Provides a single point of reference for all framework paths
     * Loads user configuration and handles getting and setting paths via keywords
     */
    class PathConfig
    {
        // -----------------------------------------------------------------------------------------------------------------
        // properties

            /**
             * Static instance property
             * @var PathConfig
             */
            protected static $_instance;

            /**
             * Array of system paths
             * @var array
             */
            protected $paths = array();

            /**
             * Boolean to set use of safe realpath
             * @var
             */
            public $safepath;


        // -----------------------------------------------------------------------------------------------------------------
        // instantiation

            /**
             * Static get instance method
             * @return PathConfig
             */
            public static function instance()
            {
                if( ! static::$_instance )
                {
                    static::$_instance = new self;
                }
                return static::$_instance;
            }

            /**
             * Constructor
             */
            protected function __construct()
            {
                static::$_instance = $this;
            }


        // -----------------------------------------------------------------------------------------------------------------
        // public methods

            /**
             * Initialize the class and load the paths config
             *
             * This function MUST be called before using the class
             *
             * @param string    $basePath       The absolute path TO the base folder from the PathConfig location
             * @param string    $configPath     An optional relative path FROM the base folder TO the folder of the `paths.php` configuration file. Leave empty to use the base folder.
             * @return PathConfig
             * @throws \InvalidArgumentException
             */
            public function load($basePath = null, $configPath = null)
            {
                // resolve base path
                $base = $basePath ?: $this->root();

                // base path
                if( ! $base )
                {
                    throw new \InvalidArgumentException('Base path "' .$basePath. '" doesn\'t resolve to a folder');
                }

                // set base path
                $base = $this->paths['base'] = $this->fix($base . '/');

                // config path
                $config = realpath($base . $configPath . '/paths.php');
                if( ! $config )
                {
                    throw new \InvalidArgumentException('Config path "' .$base .$configPath. '" doesn\'t resolve to a "paths.php" configuration file');
                }

                // load config
                $paths = require $config;

                // debug windows / unc
                //$this->paths['base'] = 'c:/path/to/server/';
                //$this->paths['base'] = '//SERVER/path/to/server/';

                foreach($paths as $key => $value)
                {
                    $this->set($key, $value);
                }

                // return this
                return $this;
            }

            /**
             * Gets a path
             *
             * @param string	$key		Optional key to the preset folder or file, i.e. 'config'
             * @param string	$filepath 	Only if key exists, optional path to append to the returned root path, i.e. 'view.php'
             * @return string				The final folder or file path
             */
            public function get($key = '', $filepath = '')
            {
                // default to base path for no arguments
                if($key === '' && $filepath === '')
                {
                    $path = $this->paths['base'];
                }

                // pick a configured path if a key is passed
                else if(isset($this->paths[$key]))
                {
                    $path = $this->realpath($this->paths[$key] . $filepath, true);
                }

                // otherwise, make a path from root
                else
                {
                    $path = $this->make($key);
                }

                // return
                return $path;
            }

            /**
             * Sets a path
             *
             * Note that the path is relative from the base folder
             * Only allows setting of paths that don't yet exist
             *
             * @param string    $key        The path's key i.e. 'config'
             * @param string    $value      The path's value, i.e. 'support/config/'
             * @return bool                 true or false if set
             */
            public function set($key, $value)
            {
                if( ! array_key_exists($key, $this->paths) )
                {
                    $this->paths[$key] = $this->make($value);
                    return true;
                }
                return false;
            }

            /**
             * Gets the current paths array
             *
             * @return array
             */
            public function all()
            {
                return $this->paths;
            }


        // -----------------------------------------------------------------------------------------------------------------
        // utility functions

            protected function root()
            {
                $path = __DIR__;
                $last = '';
                while($path !== $last)
                {
                    $last = $path;
                    $path = realpath($path . '../');
                    if(file_exists($path . '/paths.php'))
                    {
                        return $path;
                    }
                }
                return false;
            }

            /**
             * Utility function to build a path from the base path
             *
             * @param string $path
             * @return string
             */
            protected function make($path)
            {
                return $this->realpath($this->paths['base'] . $this->fix($path));
            }

            /**
             * Utility function to convert any path to the OS format
             *
             * @param string $path
             * @return string
             */
            protected function fix($path)
            {
                return preg_replace('%[\/]%', DIRECTORY_SEPARATOR, $path);
            }

            /**
             * Safe version of PHP realpath that doesn't return null for non-existant paths
             *
             * @param $path
             * @return string
             */
            protected function realpath($path, $fix = false)
            {
                // fix?
                if($fix)
                {
                    $path = $this->fix($path);
                }

                // safe?
                if($this->safepath)
                {
                    return realpath($path);
                }

                // variables
                $output = $input = $path;

                // test path
                if(preg_match('%^.{1,2}/|/\.{1,2}|/{2,}/%', $input))
                {
                    // respect drive or UNC
                    $root = '';
                    if(preg_match('%^(\w:/|//\w+/)(.*)%', $input, $matches))
                    {
                        $root = $matches[1];
                        $input = $matches[2];
                    }

                    // split input
                    $src = explode('/', $input);

                    // build output
                    $trg = array();
                    while(count($src))
                    {
                        $str = array_shift($src);
                        if($str == '.' || $str == '')continue;
                        else if($str == '..')array_pop($trg);
                        else $trg[] = $str;
                    }

                    // convert to string
                    $output = implode('/', $trg);

                    // re-add drive or UNC
                    $output = $root . $output;

                    // respect leading and trailing slashes
                    if(substr($input, 0, 1) == '/')$output = '/' . $output;
                    if(substr($input, -1) == '/' && substr($output, -1) != '/')$output = $output . '/';
                }

                // output
                return $output;
            }

    }

}

namespace{

    /**
     * Helper function to shortcut to PathConfig::get()
     *
     * @param string $key
     * @param string $filepath
     * @return mixed
     */
    function path($key = '', $filepath = '')
    {
        return pathconfig\PathConfig::instance()->get($key, $filepath);
    }

}
