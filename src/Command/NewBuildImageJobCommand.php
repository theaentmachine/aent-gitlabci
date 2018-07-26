<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use \TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractJsonEventCommand;

final class NewBuildImageJobCommand extends AbstractJsonEventCommand
{
    protected function getEventName(): string
    {
        return CommonEvents::NEW_BUILD_IMAGE_JOB_EVENT;
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

        $registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);

        $aentHelper->spacer();
        $this->output->writeln("🦊 Dockerfile: <info>TODO</info>");
        $aentHelper->spacer();

        $file = new GitLabCIFile();
        $file->findOrCreate();

        $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> has been successfully updated!');

        return null;
    }
}
