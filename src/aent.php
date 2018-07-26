#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use TheAentMachine\AentApplication;
use TheAentMachine\AentGitLabCI\Command\AddEventCommand;
use TheAentMachine\AentGitLabCI\Command\NewBuildImageJobCommand;
use TheAentMachine\AentGitLabCI\Command\NewDeployDockerComposeJobEventCommand;

$application = new AentApplication();

$application->add(new AddEventCommand());
$application->add(new NewBuildImageJobCommand());
$application->add(new NewDeployDockerComposeJobEventCommand());

$application->run();
