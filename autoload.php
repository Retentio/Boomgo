<?php

spl_autoload_register(function($class) {
    
    if (0 === strpos($class, 'Boomgo\\tests\\units')) {

        require_once __DIR__.'/tests/'.implode('/', array_slice(explode('\\', $class), 2)).'.php';
        return true;
    } else if (0 === strpos($class, 'Boomgo')) {

        require_once __DIR__.'/src/'.implode('/', explode('\\', $class)).'.php';
        return true;
    }
});