<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI;

use Symfony\Component\Yaml\Yaml;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\AbstractBuildJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\AbstractDeployJob;
use TheAentMachine\Aenthill\Pheromone;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\YamlTools\YamlTools;

final class GitLabCIFile
{
    public const DEFAULT_FILENAME = '.gitlab-ci.yml';

    /** @var string */
    private $path;

    /** @var \SplFileInfo */
    private $file;

    /**
     * GitLabCIFile constructor.
     * @throws MissingEnvironmentVariableException
     */
    public function __construct()
    {
        $this->path = Pheromone::getContainerProjectDirectory() . '/' . self::DEFAULT_FILENAME;
    }

    public function exist(): bool
    {
        return \file_exists($this->path);
    }

    /**
     * @return GitLabCIFile
     * @throws GitLabCIFileException
     */
    public function findOrCreate(): self
    {
        if (!$this->exist()) {
            return $this->create()->addStages();
        }

        $this->file = new \SplFileInfo($this->path);
        return $this;
    }

    private function create(): self
    {
        if ($this->exist()) {
            return $this;
        }

        \file_put_contents($this->path, '');

        $fileOwner = \fileowner(\dirname($this->path));
        if (!is_bool($fileOwner)) {
            \chown($this->path, $fileOwner);
        }

        $fileGroup = \filegroup(\dirname($this->path));
        if (!is_bool($fileGroup)) {
            \chgrp($this->path, $fileGroup);
        }

        $this->file = new \SplFileInfo($this->path);

        return $this;
    }

    /**
     * @return GitLabCIFile
     * @throws GitLabCIFileException
     */
    private function addStages(): self
    {
        if (!$this->exist()) {
            throw GitLabCIFileException::missingFile();
        }

        $stages = [
            'stages' => [
                'test',
                'build',
                'deploy',
                'cleanup',
            ],
        ];

        $yaml = Yaml::dump($stages, 256, 2, Yaml::DUMP_OBJECT_AS_MAP);
        \file_put_contents($this->path, $yaml);

        return $this;
    }

    /**
     * @param AbstractBuildJob $job
     * @return GitLabCIFile
     * @throws GitLabCIFileException
     */
    public function addBuild(AbstractBuildJob $job): self
    {
        if (!$this->exist()) {
            throw GitLabCIFileException::missingFile();
        }

        $yaml = Yaml::dump($job->dump(), 256, 2, Yaml::DUMP_OBJECT_AS_MAP);
        YamlTools::mergeContentIntoFile($yaml, $this->path);

        return $this;
    }

    /**
     * @param AbstractDeployJob $job
     * @return GitLabCIFile
     * @throws GitLabCIFileException
     */
    public function addDeploy(AbstractDeployJob $job): self
    {
        if (!$this->exist()) {
            throw GitLabCIFileException::missingFile();
        }

        $yaml = Yaml::dump($job->dump(), 256, 2, Yaml::DUMP_OBJECT_AS_MAP);
        YamlTools::mergeContentIntoFile($yaml, $this->path);

        return $this;
    }
}
