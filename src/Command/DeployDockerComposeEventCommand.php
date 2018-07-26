<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\DeployDockerComposeJob;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use \TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Metadata as AentConsoleMetadata;
use TheAentMachine\Command\JsonEventCommand;

final class DeployDockerComposeEventCommand extends JsonEventCommand
{
    protected function getEventName(): string
    {
        return 'DEPLOY_DOCKER_COMPOSE';
    }

    /**
     * @param array<string,string> $payload
     * @return array|null
     * @throws GitLabCIFileException
     * @throws PayloadException
     * @throws JobException
     */
    protected function executeJsonEvent(array $payload): ?array
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->title("GitLab CI: adding a deploy stage");

        $this->validatePayload($payload);
        $dockerComposeFilename = $this->getDockerComposeFilename($payload);
        $envName = $this->getEnvName($payload);
        $envType = $this->getEnvType($payload);
        $registryDomainName = Manifest::getMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);

        $aentHelper->spacer();
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

    /**
     * @param array<string,string> $payload
     * @throws PayloadException
     */
    private function validatePayload(array $payload): void
    {
        if (!isset($payload[AentConsoleMetadata::DOCKER_COMPOSE_FILENAME_KEY])) {
            throw PayloadException::missingKey(AentConsoleMetadata::DOCKER_COMPOSE_FILENAME_KEY);
        }

        if (!isset($payload[AentConsoleMetadata::ENV_NAME_KEY])) {
            throw PayloadException::missingKey(AentConsoleMetadata::ENV_NAME_KEY);
        }

        if (!isset($payload[AentConsoleMetadata::ENV_TYPE_KEY])) {
            throw PayloadException::missingKey(AentConsoleMetadata::ENV_TYPE_KEY);
        }
    }

    /**
     * @param array<string,string> $payload
     * @return string
     */
    private function getDockerComposeFilename(array $payload): string
    {
        return $payload[AentConsoleMetadata::DOCKER_COMPOSE_FILENAME_KEY];
    }

    /**
     * @param array<string,string> $payload
     * @return string
     */
    private function getEnvName(array $payload): string
    {
        return $payload[AentConsoleMetadata::ENV_NAME_KEY];
    }

    /**
     * @param array<string,string> $payload
     * @return string
     */
    private function getEnvType(array $payload): string
    {
        return $payload[AentConsoleMetadata::ENV_TYPE_KEY];
    }

    private function askForRemoteIP(): string
    {
        return $this->getAentHelper()->question('Remote IP')
            ->setHelpText('The IP of the server where you want to deploy your Docker Compose stack.')
            ->compulsory()
            ->setValidator(function (string $value) {
                $value = trim($value);
                if (!\preg_match('/^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $value)) {
                    throw new \InvalidArgumentException('Invalid IP "' . $value . '".');
                }
                return $value;
            })
            ->ask();
    }

    private function askForRemoteUser(): string
    {
        return $this->getAentHelper()->question('Remote user')
            ->setHelpText('The username of the user which will deploy over SSH your Docker Compose stack.')
            ->compulsory()
            ->setValidator(function (string $value) {
                $value = trim($value);
                if (!\preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
                    throw new \InvalidArgumentException('Invalid username "' . $value . '". User names can contain alphanumeric characters, and "_", "-".');
                }
                return $value;
            })
            ->ask();
    }

    private function askForRemotePath(): string
    {
        return $this->getAentHelper()->question('Remote path')
            ->setHelpText('The absolute path (without trailing "/") on the server where your Docker Compose stack will be deployed.')
            ->compulsory()
            ->setValidator(function (string $value) {
                $value = trim($value);
                if (!\preg_match('/^[\'"]?(?:\/[^\/\n]+)*[\'"]?$/', $value)) {
                    throw new \InvalidArgumentException('Invalid path "' . $value . '". It has to be absolute avec without trailing "/".');
                }
                return $value;
            })
            ->ask();
    }

    private function askForBranch(string $envType): string
    {
        $question = $this->getAentHelper()->question('Git branch')
            ->compulsory()
            ->setValidator(function (string $value) {
                $value = trim($value);
                if (!\preg_match('/^[a-zA-Z0-9_.-]+$/', $value)) {
                    throw new \InvalidArgumentException('Invalid branch name "' . $value . '". Branch names can contain alphanumeric characters, and "_", ".", "-".');
                }
                return $value;
            });
        if ($envType === AentConsoleMetadata::ENV_TYPE_PROD) {
            $question->setDefault('master');
        }
        return $question->ask();
    }
}
