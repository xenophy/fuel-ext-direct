<?php

namespace Extdirect;

// {{{ Extdirect

/**
 * Extdirect
 *
 * Copyright (c) 2016 Xenophy Entertainment.CO.,LTD All rights Reserved.
 * http://www.xenophy-entertainment.com
 */
class Extdirect {

    // {{{ boot

    /**
     * initialize boot function
     */
    static function boot() {

        // load config
        \Config::load('extdirect', true);

        // get route
        $route = \Config::get('extdirect.route', 'direct');

        // add routes
        \Router::add($route . '/(:any)', 'extdirect/$1');
        \Router::add($route, 'extdirect/index');

    }

    // }}}

}

// }}}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */