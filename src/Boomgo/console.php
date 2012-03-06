<?php

set_time_limit(0);

require __DIR__.'/../../vendor/.composer/autoload.php';

$application = new \Symfony\Component\Console\Application('Boomgo Console', '1.0.0');
$application->add(new \Boomgo\Console\Command\MapperGeneratorCommand('boomgo:generate'));
$application->run();