<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractBuildJob extends AbstractJob
{
    public function __construct(string $serviceName)
    {
        $this->jobName = "build_$serviceName";
        $this->stage = 'build';
    }
}
