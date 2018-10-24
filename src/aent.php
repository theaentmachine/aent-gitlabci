#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use \TheAentMachine\Aent\CIAent;
use \TheAentMachine\AentGitLabCI\Event\ConfigureCIEvent;
use \TheAentMachine\AentGitLabCI\Event\DockerComposeDeployJobEvent;
use \TheAentMachine\AentGitLabCI\Event\KubernetesDeployJobEvent;
use \TheAentMachine\AentGitLabCI\Event\BuildJobEvent;

$application = new CIAent('GitLab', new ConfigureCIEvent(), new DockerComposeDeployJobEvent(), new KubernetesDeployJobEvent(), new BuildJobEvent());
$application->run();
