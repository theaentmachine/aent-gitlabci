<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Dependency;
use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\EventCommand;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;

class AddEventCommand extends EventCommand
{
    protected function getEventName(): string
    {
        return 'ADD';
    }

    /**
     * @param null|string $payload
     * @return null|string
     * @throws GitLabCIFileException
     */
    protected function executeEvent(?string $payload): ?string
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->title('Installing GitLab CI file');

        $file = new GitLabCIFile();
        if ($file->exist()) {
            $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> found!');
        } else {
            $file->findOrCreate();
            $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> was successfully created!');
        }

        $aentHelper->spacer();

        if (null === Manifest::getMetadataOrNull(Metadata::REGISTRY_DOMAIN_NAME_KEY)) {
            $registryDomainName = $this->askForRegistryDomainName();
            Manifest::addMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY, $registryDomainName);
        }

        if (null === Manifest::getDependencyOrNull(Dependency::AENT_DOCKERFILE_KEY)) {
            Manifest::addDependency('theaentmachine/aent-dockerfile', Dependency::AENT_DOCKERFILE_KEY, null);
        }

        return null;
    }

    private function askForRegistryDomainName(): string
    {
        return $this->getAentHelper()->question('Registry domain name')
            ->setHelpText('The domain name of the Docker Container Registry integrated with your Git repository on your GitLab. This is the space where your Docker images are stored.')
            ->compulsory()
            ->setValidator(function (string $value) {
                $value = trim($value);
                if (!\preg_match('/^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9][a-zA-Z0-9-_]+\.[a-zA-Z]{2,11}?:[0-9]*$/im', $value)) {
                    throw new \InvalidArgumentException('Invalid domain name "' . $value . '". Note: the domain name must not start with "http(s)://"');
                }
                return $value;
            })
            ->ask();
    }
}
