<?php

// Including Atoum
require_once __DIR__.'/../vendor/mageekguy.atoum.phar';

// Boomgo classes autoload
if (file_exists($file = __DIR__.'/../autoload.php')) {
    require_once $file;
} elseif (file_exists($file = __DIR__.'/../autoload.php.dist')) {
    require_once $file;
}