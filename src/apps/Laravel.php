<?php

namespace pathconfig\apps;

/**
 * Class LaravelApp
 * @package davestewart\pathconfig
 */
class Laravel extends Lumen
{

    public function __construct()
    {
        // super
        parent::__construct();

        // load
        var_dump('Laravel App');
    }

}