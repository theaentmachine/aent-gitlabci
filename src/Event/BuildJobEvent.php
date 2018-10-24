<?php

namespace TheAentMachine\AentGitLabCI\Event;

use Safe\Exceptions\FilesystemException;
use TheAentMachine\Aent\Event\CI\AbstractCIBuildJobEvent;
use TheAentMachine\Aent\Payload\CI\CINewImageReplyPayload;
use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\BuildDockerfileJob;
use TheAentMachine\Exception\MissingEnvironmentVariableException;

final class BuildJobEvent extends AbstractCIBuildJobEvent
{
    /**
     * @param string $serviceName
     * @param string $dockerfileName
     * @return CINewImageReplyPayload
     * @throws FilesystemException
     * @throws GitLabCIFileException
     * @throws JobException
     * @throws MissingEnvironmentVariableException
     */
    protected function addBuildJob(string $serviceName, string $dockerfileName): CINewImageReplyPayload
    {
        $this->prompt->printAltBlock("GitLab: adding build job...");
        $context = BaseGitLabCIContext::fromMetadata();
        $job = new BuildDockerfileJob(
            $serviceName,
            $dockerfileName,
            $context
        );
        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addBuild($job);
        return new CINewImageReplyPayload($job->getDockerImageName());
    }
}
