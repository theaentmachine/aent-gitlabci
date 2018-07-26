<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\DeployDockerComposeJob;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use \TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractEventCommand;
use TheAentMachine\Question\CommonValidators;

final class NewDeployDockerComposeJobEventCommand extends AbstractEventCommand
{
    protected function getEventName(): string
    {
        return CommonEvents::NEW_DEPLOY_DOCKER_COMPOSE_JOB_EVENT;
    }

    /**
     * @param string|null $payload
     * @return array|null
     * @throws GitLabCIFileException
     * @throws PayloadException
     * @throws JobException
     */
    protected function executeEvent(?string $payload): ?string
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->title("GitLab CI: adding a deploy stage");

        if (empty($payload)) {
            throw PayloadException::missingDockerComposeFilename();
        }

        $dockerComposeFilename = $payload;
        $envName = Manifest::mustGetMetadata(CommonMetadata::ENV_NAME_KEY);
        $envType = Manifest::mustGetMetadata(CommonMetadata::ENV_TYPE_KEY);
        $registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);

        $this->output->writeln("🦊 Docker Compose file: <info>$dockerComposeFilename</info>");
        $aentHelper->spacer();

        $remoteIP = $this->askForRemoteIP();
        $remoteUser = $this->askForRemoteUser();
        $remotePath = $this->askForRemotePath();
        $branch = $this->askForBranch($envType);

        $instructions = new DeployDockerComposeJob($envName, $registryDomainName, $dockerComposeFilename, $remoteIP, $remoteUser, $remotePath);
        $instructions->addOnly($branch);

        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addDeploy($instructions);

        $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> has been successfully updated!');

        return null;
    }

    private function askForRemoteIP(): string
    {
        return $this->getAentHelper()->question('Remote IP')
            ->setHelpText('The IP of the server where you want to deploy your Docker Compose stack.')
            ->compulsory()
            ->setValidator(CommonValidators::getIPv4Validator())
            ->ask();
    }

    private function askForRemoteUser(): string
    {
        return $this->getAentHelper()->question('Remote user')
            ->setHelpText('The username of the user which will deploy over SSH your Docker Compose stack.')
            ->compulsory()
            ->setValidator(CommonValidators::getAlphaValidator(['_', '-'], 'User names can contain alphanumeric characters and "_", "-".'))
            ->ask();
    }

    private function askForRemotePath(): string
    {
        return $this->getAentHelper()->question('Remote path')
            ->setHelpText('The absolute path (without trailing "/") on the server where your Docker Compose stack will be deployed.')
            ->compulsory()
            ->setValidator(CommonValidators::getAbsolutePathValidator())
            ->ask();
    }

    private function askForBranch(string $envType): string
    {
        $question = $this->getAentHelper()->question('Git branch')
            ->setHelpText('Whenever the given branch is updated, your Docker Compose stack will be redeployed.')
            ->compulsory()
            ->setValidator(CommonValidators::getAlphaValidator(['_', '.', '-'], 'Branch names can contain alphanumeric characters and "_", ".", "-".'));

        if ($envType === CommonMetadata::ENV_TYPE_PROD) {
            $question->setDefault('master');
        }

        return $question->ask();
    }
}
