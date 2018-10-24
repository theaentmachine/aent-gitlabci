<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractDeployJob extends AbstractJob
{
    /**
     * AbstractDeployJob constructor.
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->jobName = "deploy_$identifier";
        $this->stage = 'deploy';
    }
}
