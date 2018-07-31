<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

abstract class AbstractBuildJob extends AbstractJob
{
    /** @var bool */
    protected $isVariableEnvironment;

    /** @var string */
    protected $dockerImageName;

    public function __construct(string $envName, string $serviceName)
    {
        $this->jobName = "build_$envName" . "_$serviceName";
        $this->stage = 'build';
    }

    /**
     * @return bool
     */
    public function isVariableEnvironment(): bool
    {
        return $this->isVariableEnvironment;
    }

    /**
     * @return string
     */
    public function getDockerImageName(): string
    {
        return $this->dockerImageName;
    }
}
