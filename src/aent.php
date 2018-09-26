#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use \TheAentMachine\Aent\CIAent;
use \TheAentMachine\AentGitLabCI\Event\ConfigureCIEvent;
use \TheAentMachine\AentGitLabCI\Event\DockerComposeDeployJobEvent;

$application = new CIAent('GitLab', new ConfigureCIEvent(), new DockerComposeDeployJobEvent());
$application->run();
