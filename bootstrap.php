<?php

// {{{ bootstrap

/**
 * bootstrap
 *
 * Copyright (c) 2016 Xenophy Entertainment.CO.,LTD All rights Reserved.
 * http://www.xenophy-entertainment.com
 */

// Add core namespace
\Autoloader::add_core_namespace('Extdirect');

// Add classes
\Autoloader::add_classes(array(
    'Controller_Extdirect'      => __DIR__.'/classes/controller.php',
    'Extdirect\\Extdirect'      => __DIR__.'/classes/extdirect.php',
));

// Initialize for Ext Direct
Extdirect::boot();

// }}}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */