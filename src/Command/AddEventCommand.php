<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractEventCommand;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\Question\CommonValidators;

final class AddEventCommand extends AbstractEventCommand
{
    protected function getEventName(): string
    {
        return CommonEvents::ADD_EVENT;
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

        if (null === Manifest::getDependency(Metadata::REGISTRY_DOMAIN_NAME_KEY)) {
            $registryDomainName = $this->askForRegistryDomainName();
            Manifest::addMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY, $registryDomainName);
        }

        return null;
    }

    private function askForRegistryDomainName(): string
    {
        return $this->getAentHelper()->question('Registry domain name')
            ->setHelpText('The domain name of the Docker Container Registry integrated with your Git repository on your GitLab. This is the space where your Docker images are stored.')
            ->compulsory()
            ->setValidator(CommonValidators::getDomainNameWithPortValidator())
            ->ask();
    }
}
