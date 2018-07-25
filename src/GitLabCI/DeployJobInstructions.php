<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI;

abstract class DeployJobInstructions extends JobInstructions
{
    public function __construct(string $envName)
    {
        $this->jobName = "deploy_$envName";
        $this->stage = 'deploy';
    }
}