<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Exception\JobException;

abstract class AbstractJob
{
    /** @var string */
    protected $jobName;

    /** @var string */
    protected $image;

    /** @var string */
    protected $stage;

    /** @var string[] */
    protected $services;

    /** @var array<string,string> */
    protected $variables = [];

    /** @var string[] */
    protected $beforeScript = [];

    /** @var string[] */
    protected $script = [];

    /** @var array<string,string> */
    protected $environment = [];

    /** @var string[] */
    protected $only = [];

    /** @var string[] */
    protected $except = [];

    /** @var bool */
    protected $manual = false;

    /** @return mixed[] */
    public function dump(): array
    {
        $obj = [
            $this->jobName => [
                'image' => $this->image,
                'stage' => $this->stage,
            ]
        ];
        if ($this->hasServices()) {
            $obj[$this->jobName]['services'] = $this->services;
        }
        if ($this->hasVariables()) {
            $obj[$this->jobName]['variables'] = $this->variables;
        }
        if ($this->hasBeforeScript()) {
            $obj[$this->jobName]['before_script'] = $this->beforeScript;
        }
        if ($this->hasScript()) {
            $obj[$this->jobName]['script'] = $this->script;
        }
        if ($this->hasEnvironment()) {
            $obj[$this->jobName]['environment'] = $this->environment;
        }
        if ($this->hasOnly()) {
            $obj[$this->jobName]['only'] = $this->only;
        }
        if ($this->hasExcept()) {
            $obj[$this->jobName]['except'] = $this->except;
        }
        if ($this->manual) {
            $obj[$this->jobName]['when'] = 'manual';
        }
        return $obj;
    }

    /**
     * @param string $identifier
     * @throws JobException
     */
    public function addOnly(string $identifier): void
    {
        if (\in_array($identifier, $this->only)) {
            return;
        }
        if (\in_array($identifier, $this->except)) {
            throw JobException::cannotAddOnly($identifier);
        }
        $this->only[] = $identifier;
    }

    /**
     * @param string $identifier
     * @throws JobException
     */
    public function addExcept(string $identifier): void
    {
        if (\in_array($identifier, $this->except)) {
            return;
        }
        if (\in_array($identifier, $this->only)) {
            throw JobException::cannotAddExcept($identifier);
        }
        $this->except[] = $identifier;
    }

    /**
     * @return bool
     */
    private function hasServices(): bool
    {
        return !empty($this->services);
    }

    /**
     * @return bool
     */
    private function hasVariables(): bool
    {
        return !empty($this->variables);
    }

    /**
     * @return bool
     */
    private function hasBeforeScript(): bool
    {
        return !empty($this->beforeScript);
    }

    /**
     * @return bool
     */
    private function hasScript(): bool
    {
        return !empty($this->script);
    }

    /**
     * @return bool
     */
    private function hasEnvironment(): bool
    {
        return !empty($this->environment);
    }

    /**
     * @return bool
     */
    private function hasOnly(): bool
    {
        return !empty($this->only);
    }

    /**
     * @return bool
     */
    private function hasExcept(): bool
    {
        return !empty($this->except);
    }

    /**
     * @return string
     */
    public function getJobName(): string
    {
        return $this->jobName;
    }
}
