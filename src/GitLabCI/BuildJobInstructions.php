<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI;

abstract class BuildJobInstructions extends JobInstructions
{
    public function __construct(string $serviceName)
    {
        $this->jobName = "build_$serviceName";
        $this->stage = 'build';
    }
}