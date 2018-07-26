<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractDeployJob extends AbstractJob
{
    public function __construct(string $envName)
    {
        $this->jobName = "deploy_$envName";
        $this->stage = 'deploy';
    }
}