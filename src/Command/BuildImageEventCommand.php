<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use \TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Aenthill\Metadata as AentConsoleMetadata;
use TheAentMachine\Command\JsonEventCommand;

final class BuildImageEventCommand extends JsonEventCommand
{
    protected function getEventName(): string
    {
        return 'BUILD_IMAGE';
    }

    /**
     * @param string[] $payload
     * @return array|null
     * @throws GitLabCIFileException
     * @throws PayloadException
     * @throws JobException
     */
    protected function executeJsonEvent(array $payload): ?array
    {
        $aentHelper = $this->getAentHelper();
        $aentHelper->title("GitLab CI: adding a deploy stage");

        $registryDomainName = Manifest::getMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);

        $aentHelper->spacer();
        $this->output->writeln("🦊 Dockerfile: <info>TODO</info>");
        $aentHelper->spacer();

        $file = new GitLabCIFile();
        $file->findOrCreate();

        $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> has been successfully updated!');

        return null;
    }
}
