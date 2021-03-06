<?php

namespace pathconfig
{

    /**
     * PathConfig class
     *
     * Provides a single point of reference for all framework paths
     * Loads user configuration and handles getting and setting paths via keywords
     * Manages language and platform specifics regarding slashes
     */
    class PathConfig
    {
        // -----------------------------------------------------------------------------------------------------------------
        // properties

            /**
             * Static instance property
             *
             * @var PathConfig
             */
            protected static $_instance;

            /**
             * Array of system paths
             *
             * @var array
             */
            protected $paths = array();

            /**
             * Directory separator to use
             *
             * @var
             */
            protected $separator = DIRECTORY_SEPARATOR;


        // -----------------------------------------------------------------------------------------------------------------
        // options

            /**
             * The application's base path
             *
             * @var string
             */
            protected $basepath = '';

            /**
             * Converts slashes when setting or getting paths, defaults to true
             *
             * Options are:
             *
             *  - true      : convert slashes to the platform preference
             *  - false     : leave all slashes as supplied
             *  - "auto"    : convert to using the first slash type found in config file
             *
             * @var mixed
             */
            public $convertslashes = true;

            /**
             * Trim trailing slashes on folders, defaults to false
             *
             * @var bool
             */
            public $trimslashes = true;

            /**
             * Test paths exist when setting, defaults to false
             *
             * @var bool
             */
            public $testpaths = false;

            /**
             * Allow existing path values: to be overwritten, defaults to false
             *
             * @var bool
             */
            public $mutable = false;


        // -----------------------------------------------------------------------------------------------------------------
        // instantiation

            /**
             * Constructor
             */
            protected function __construct()
            {
                static::$_instance = $this;
            }


        // -----------------------------------------------------------------------------------------------------------------
        // configuration

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
             * Set an option
             *
             * Options are:
             *
             *  - basepath
             *  - convertslashes
             *  - trimslashes
             *  - testpaths
             *  - mutable
             *
             * Note that slash-related settings can only be set before paths are loaded
             *
             * @param string $name
             * @param mixed $value
             * @throws \Exception
             * @return $this
             */
            public function option($name, $value)
            {
                // setting name
                $name = strtolower($name);

                // disable some paths from being changed once paths are loaded
                if(count($this->paths) && in_array($name, ['basepath', 'convertslashes', 'trimslashes']))
                {
                    throw new \Exception('Option "' .$name. '" cannot be changed once paths are loaded');
                }

                // update settings
                switch($name)
                {
                    case 'convertslashes':
                        if($value === true || $value === false || $value === 'auto')
                        {
                            $this->convertslashes = $value;
                            $this->separator = DIRECTORY_SEPARATOR;
                        }
                        break;

                    case 'basepath':
                    case 'trimslashes':
                    case 'testpaths':
                    case 'mutable':
                        $this->$name = $value;
                        break;
                }
                return $this;
            }

            /**
             * Load the paths config
             *
             * This function MUST be called before using the class
             *
             * @param string    $path       An optional absolute or relative path to the `paths.php` folder. Leave empty to use the base folder
             * @throws \InvalidArgumentException
             * @return $this
             */
            public function load($path = null)
            {
                // initialize / test basebath
                $this->initialize();

                // absolute path
                if( file_exists($path) )
                {
                    $config = is_file($path)
                        ? $path
                        : realpath($path . '/paths.php');
                }

                // relative path
                else
                {
                    // test if an actual config file was passed
                    $path = preg_match('/paths\.php$/', $path)
                        ? $path
                        : $this->basepath . '/' . $path . '/paths.php';

                    // resolve real file
                    $config = realpath($path);
                    if( ! $config )
                    {
                        throw new \InvalidArgumentException('Config path "' .$path. '" doesn\'t resolve to a configuration file');
                    }
                }

                // load config
                $paths = require $config;

                // detect directory separator if convertslashes is auto
                if($this->convertslashes === 'auto')
                {
                    // set separator
                    preg_match('%[\/]%', implode('', $paths), $matches);
                    $this->separator =  count($matches) ? $matches[0] : DIRECTORY_SEPARATOR;
                }

                // convert base
                if($this->convertslashes)
                {
                    $this->basepath = $this->fix($this->basepath);
                }

                // set paths
                foreach($paths as $key => $value)
                {
                    $this->set($key, $value);
                }

                // return this
                return $this;
            }


        // -----------------------------------------------------------------------------------------------------------------
        // public methods

            /**
             * Gets a path
             *
             * @param string	$key		Optional key to the assigned folder or file, i.e. 'config'
             * @param string	$filepath 	Only if key has been supplied, an optional path to append to the returned root path, i.e. 'view.php'
             * @return string				The final folder or file path
             */
            public function get($key = '', $filepath = '')
            {
                // if no arguments, or just 'base', return basepath
                if( in_array($key.$filepath, ['base', '']) )
                {
                    $path       = $this->basepath;
                }

                // if a key is passed, pick a configured path
                else if(isset($this->paths[$key]))
                {
                    $filepath   = $filepath ? $this->separator .$filepath : '';
                    $path       = $this->make($this->paths[$key] . $filepath);
                }

                // if one argument, make a path from root
                else
                {
                    $path       = $this->make($key);
                }

                // return
                return $path;
            }

            /**
             * Sets a path
             *
             * Note that the path is relative from the base folder
             *
             * @param string    $key        The path's key i.e. 'config'
             * @param string    $value      The path's value, i.e. 'support/config/'
             * @return bool                 true or false if set
             */
            public function set($key, $value)
            {
                // ignore setting of base bath
                if($key === 'base')
                {
                    return false;
                }

                // set path
                if($this->mutable || ! array_key_exists($key, $this->paths) )
                {
                    $this->paths[$key] = $this->fix($value);
                    return true;
                }
                return false;
            }

            /**
             * Gets the current paths array
             *
             * @param bool $full
             * @return array
             */
            public function all($full = true)
            {
                // add basepath to the array
                $paths = array( 'base' => $this->basepath );

                // add rest of paths
                foreach($this->paths as $key => $path)
                {
                    $paths[$key] = $full
                        ? $this->make($path)
                        : $path;
                }

                // return
                return $paths;
            }


        // -----------------------------------------------------------------------------------------------------------------
        // utility functions

            /**
             * Initialize the library by attempting to locate the base path with `paths.php` in it
             */
            protected function initialize()
            {
                // if basepath hasn't been set, attempt to discover it by working back up the directory structure
                if( ! $this->basepath )
                {
                    $path = __DIR__;
                    $last = '';
                    while($path !== $last)
                    {
                        $last   = $path;
                        $path   = realpath($path . '/../');
                        $config = $path . '/paths.php';
                        if(file_exists($config))
                        {
                            $this->option('basepath', $this->fix($path));
                            break;
                        }
                    }

                    // check if the paths configuration was found
                    if( ! file_exists($this->basepath) )
                    {
                        throw new \Exception('The paths configuration file `paths.php` was not found anywhere on the path "' .__DIR__. '" ');
                    }

                }

                // test that the base path (supplied or found) exists
                if( ! file_exists($this->basepath) )
                {
                    throw new \Exception('The supplied base path "' .$this->basepath. '" does not exist');
                }
            }

            /**
             * Utility function to massage path into the format specified by settings
             *
             * @param string $path
             * @return string
             */
            protected function fix($path)
            {
                // convert slashes
                if($this->convertslashes)
                {
                    $path = preg_replace('%[/\\\\]%', $this->separator, $path);
                }

                // trim trailing slashes
                if($this->trimslashes)
                {
                    $path = preg_replace('%[/\\\\]+$%', '', $path);
                }

                // return
                return $path;
            }

            /**
             * Utility function to build a path from the base path
             *
             * @param string $path
             * @return string
             */
            protected function make($path)
            {
                return $this->real($this->basepath . $this->separator . $path);
            }

            /**
             * Safe version of PHP realpath() that doesn't return null for non-existant paths
             *
             * @param string $source
             * @return string
             */
            protected function real($source)
            {
                // variables
                $output = $source;
                $input  = str_replace('\\', '/', $source);

                // test input
                if(preg_match('%^.{1,2}/|/\.{1,2}|/{2,}/%', $input))
                {
                    // variables
                    $root   = '';
                    $path   = $input;

                    // respect drive or UNC
                    if(preg_match('%^(\w:/|//\w+/)(.*)%', $input, $matches))
                    {
                        $root = $matches[1];
                        $path = $matches[2];
                    }

                    // split input
                    $src = explode('/', $path);

                    // build output
                    $trg = array();
                    while(count($src))
                    {
                        $str = array_shift($src);
                        if($str == '.' || $str == '')
                        {
                            continue;
                        }
                        else if($str == '..')
                        {
                            array_pop($trg);
                        }
                        else
                        {
                            $trg[] = $str;
                        }
                    }

                    // convert to string
                    $output = implode('/', $trg);

                    // re-add drive or UNC
                    $output = $root . $output;

                    // respect leading and trailing slashes
                    if(substr($input, 0, 1) == '/')
                    {
                        $output = '/' . $output;
                    }
                    if(substr($input, -1) == '/' && substr($output, -1) != '/')
                    {
                        $output = $output . '/';
                    }

                    // if windows format, convert back
                    if($this->separator === '\\')
                    {
                        $output = str_replace('/', '\\', $output);
                    }
                }

                // test path
                if($this->testpaths)
                {
                    if( ! file_exists($output) )
                    {
                        $output = false;
                    }
                }

                // output
                return $output;
            }

            /**
             * Helper function to create global function alias to PathConfig::get()
             *
             * @param string $name
             */
            public static function alias($name = 'path')
            {
                eval('namespace { function ' .$name. '($key = "", $filepath = ""){ return pathconfig\PathConfig::instance()->get($key, $filepath); } }');
            }

    }

}
