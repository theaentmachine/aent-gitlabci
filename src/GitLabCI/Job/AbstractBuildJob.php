<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractBuildJob extends AbstractJob
{
    /** @var bool */
    protected $isSingleBranch;
    /** @var string */
    protected $dockerImageName;

    public function __construct(string $envName, string $serviceName)
    {
        $this->jobName = "build_$envName" . "_$serviceName";
        $this->stage = 'build';
    }

    public function isSingleBranch(): bool
    {
        return $this->isSingleBranch;
    }

    public function getDockerImageName(): string
    {
        return $this->dockerImageName;
    }
}
