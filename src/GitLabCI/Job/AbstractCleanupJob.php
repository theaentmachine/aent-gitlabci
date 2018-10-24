<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractCleanupJob extends AbstractJob
{
    /**
     * AbstractCleanupJob constructor.
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->jobName = "cleanup_$identifier";
        $this->stage = 'cleanup';
    }
}
