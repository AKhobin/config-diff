#!/usr/bin/env php
<?php
// application.php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application('config-diff', 1.0);

$command = new \ConfigManager\Commands\ExportCommand();
$application->add($command);
$application->setDefaultCommand($command->getName(), true);
$application->run();