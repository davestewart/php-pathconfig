<?php

    /**
     * Lumen 5.0 application paths
     *
     * Edit these as required, then copy to root folder and rename as `paths.php`
     */
    return array
    (
        // app
        'app'    		=> 'app/',
        'public'   		=> 'public/',

        // routes
        'routes.php'    => 'app/Http/routes.php',

        // supporting files
        'database'      => 'support/database/',
        'storage'       => 'support/storage/',
        'tests'         => 'support/tests/',

        // optional config path. uncomment to use config folder, then copy files from `vendor/laravel/lumen-framework/config` to get started
        //'config'      => 'support/config/',

        // resources
        'resources'     => 'resources/',
        'lang'          => 'resources/lang/',
        'views'         => 'resources/views/'
    );
