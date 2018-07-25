#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use TheAentMachine\AentApplication;
use TheAentMachine\AentGitLabCI\Command\AddEventCommand;
use TheAentMachine\AentGitLabCI\Command\BuildImageEventCommand;
use TheAentMachine\AentGitLabCI\Command\DeployDockerComposeEventCommand;

$application = new AentApplication();

$application->add(new AddEventCommand());
$application->add(new BuildImageEventCommand());
$application->add(new DeployDockerComposeEventCommand());

$application->run();
