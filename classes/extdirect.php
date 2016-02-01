<?php

namespace Extdirect;


class Extdirect {

    static function boot() {

        \Config::load('extdirect', true);

        $route = \Config::get('extdirect.route');

        \Router::add($route . '/(:any)', 'extdirect/$1');
        \Router::add($route, 'extdirect/index');

    }

}

