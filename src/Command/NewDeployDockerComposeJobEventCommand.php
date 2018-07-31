<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\DeployDockerComposeJob;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use \TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\Question\GitLabCICommonQuestions;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractEventCommand;
use TheAentMachine\Command\AbstractJsonEventCommand;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Exception\MissingEnvironmentVariableException;

final class NewDeployDockerComposeJobEventCommand extends AbstractEventCommand
{
    /** @var string */
    private $envName;

    /** @var string */
    private $registryDomainName;

    /** @var string */
    private $dockerComposeFilename;

    protected function getEventName(): string
    {
        return CommonEvents::NEW_DEPLOY_DOCKER_COMPOSE_JOB_EVENT;
    }

    /**
     * @param null|string $payload
     * @return null|string
     * @throws GitLabCIFileException
     * @throws JobException
     * @throws ManifestException
     * @throws MissingEnvironmentVariableException
     * @throws PayloadException
     */
    protected function executeEvent(?string $payload): ?string
    {
        $aentHelper = $this->getAentHelper();

        $aentHelper->title('GitLab CI: adding a deploy stage');

        if (empty($payload)) {
            throw PayloadException::missingDockerComposeFilename();
        }


        $this->envName = Manifest::mustGetMetadata(CommonMetadata::ENV_NAME_KEY);
        $this->registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);
        $this->dockerComposeFilename = $payload;

        $this->output->writeln("🦊 Docker Compose file: <info>$this->dockerComposeFilename</info>");
        $aentHelper->spacer();

        $job = $this->askForDeployType();

        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addDeploy($job);

        $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> has been successfully updated!');

        return null;
    }

    /**
     * @return DeployDockerComposeJob
     * @throws JobException
     */
    private function askForDeployType(): DeployDockerComposeJob
    {
        $deployType = Manifest::getMetadata(Metadata::DEPLOY_TYPE_KEY);

        if (empty($deployType)) {
            $deployType = $this->getAentHelper()
                ->choiceQuestion('Select on which provider you want to deploy your stack', [
                    Metadata::DEPLOY_TYPE_REMOTE_SERVER
                ])
                ->ask();
        }

        switch ($deployType) {
            case Metadata::DEPLOY_TYPE_REMOTE_SERVER:
                return $this->createDeployOnRemoveServerJob();
            default:
                throw JobException::unknownDeployType($deployType);
        }
    }

    /**
     * @return DeployDockerComposeJob
     * @throws JobException
     */
    private function createDeployOnRemoveServerJob(): DeployDockerComposeJob
    {
        Manifest::addMetadata(Metadata::DEPLOY_TYPE_KEY, Metadata::DEPLOY_TYPE_REMOTE_SERVER);

        $gitlabCICommonQuestions = new GitLabCICommonQuestions($this->getAentHelper());

        $remoteIP = $gitlabCICommonQuestions->askForRemoteIP();
        $remoteUser = $gitlabCICommonQuestions->askForRemoteUser();
        $remoteBasePath = $gitlabCICommonQuestions->askForRemoteBasePath();
        $branches = $gitlabCICommonQuestions->askForBranches(false);
        $isManual = $gitlabCICommonQuestions->askForManual();

        return DeployDockerComposeJob::newDeployOnRemoteServer(
            $this->envName,
            $this->registryDomainName,
            $this->dockerComposeFilename,
            $remoteIP,
            $remoteUser,
            $remoteBasePath,
            $branches,
            $isManual
        );
    }
}
