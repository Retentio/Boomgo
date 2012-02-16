<?php

if (file_exists($file = __DIR__.'/../../../vendor/.composer/autoload.php')) {
    $loader = require $file;
    
    $loader->add('Boomgo\\tests', 'tests');
    $loader->register();
} else {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}