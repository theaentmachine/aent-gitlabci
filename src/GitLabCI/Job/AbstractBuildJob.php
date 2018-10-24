<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractBuildJob extends AbstractJob
{
    /** @var bool */
    protected $isSingleBranch;

    /** @var string */
    protected $dockerImageName;

    /**
     * AbstractBuildJob constructor.
     * @param string $envName
     * @param string $serviceName
     */
    public function __construct(string $envName, string $serviceName)
    {
        $this->jobName = "build_$envName" . "_$serviceName";
        $this->stage = 'build';
    }

    /**
     * @return bool
     */
    public function isSingleBranch(): bool
    {
        return $this->isSingleBranch;
    }

    /**
     * @return string
     */
    public function getDockerImageName(): string
    {
        return $this->dockerImageName;
    }
}
