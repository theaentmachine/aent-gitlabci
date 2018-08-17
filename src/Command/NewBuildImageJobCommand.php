<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\BuildDockerfileJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractJsonEventCommand;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Exception\MissingEnvironmentVariableException;

final class NewBuildImageJobCommand extends AbstractJsonEventCommand
{
    protected function getEventName(): string
    {
        return CommonEvents::NEW_BUILD_IMAGE_JOB_EVENT;
    }

    /**
     * @param array<string,string> $payload
     * @return array|null
     * @throws GitLabCIFileException
     * @throws PayloadException
     * @throws JobException
     * @throws ManifestException
     * @throws MissingEnvironmentVariableException
     */
    protected function executeJsonEvent(array $payload): ?array
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->title('GitLab CI: adding a build stage');

        $envName = Manifest::mustGetMetadata(CommonMetadata::ENV_NAME_KEY);
        $registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);
        $projectGroup = Manifest::mustGetMetadata(Metadata::PROJECT_GROUP_KEY);
        $projectName = Manifest::mustGetMetadata(Metadata::PROJECT_NAME_KEY);
        $branchesModel = BranchesModel::newFromMetadata();

        $this->validatePayload($payload);
        $serviceName = $payload['serviceName'];
        $dockerfileName = $payload['dockerfileName'];

        $aentHelper->spacer();
        $this->output->writeln("🦊 Dockerfile: <info>$dockerfileName</info>");
        $aentHelper->spacer();

        $job = new BuildDockerfileJob(
            $envName,
            $serviceName,
            $registryDomainName,
            $projectGroup,
            $projectName,
            $dockerfileName,
            $branchesModel
        );

        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addBuild($job);

        $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> has been successfully updated!');

        return [
            'dockerImageName' => $job->getDockerImageName()
        ];
    }

    /**
     * @param array<string,string> $payload
     * @throws PayloadException
     */
    private function validatePayload(array $payload): void
    {
        if (empty($payload)) {
            throw PayloadException::emptyPayload($this->getEventName());
        }

        if (!isset($payload['serviceName'])) {
            throw PayloadException::missingServiceName();
        }

        if (!isset($payload['dockerfileName'])) {
            throw PayloadException::missingDockerfileName();
        }
    }
}
