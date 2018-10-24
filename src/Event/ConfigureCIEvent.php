<?php

namespace TheAentMachine\AentGitLabCI\Event;

use TheAentMachine\Aent\Event\CI\AbstractCIConfigureCIEvent;
use TheAentMachine\AentGitLabCI\Event\Model\Configure;
use TheAentMachine\Prompt\Helper\ValidatorHelper;

final class ConfigureCIEvent extends AbstractCIConfigureCIEvent
{
    /**
     * @return array<string,string>
     */
    protected function getMetadata(): array
    {
        $registryDomainName = $this->getRegistryDomainName();
        $projectGroup = $this->getProjectGroup();
        $projectName = $this->getProjectName();
        $configure = new Configure($registryDomainName, $projectGroup, $projectName);
        return $configure->toArray();
    }

    /**
     * @return string
     */
    private function getRegistryDomainName(): string
    {
        $text = "\nYour registry URL";
        $helpText = "The URL of the <info>Docker Container Registry</info> integrated with your Git repository on your <info>GitLab</info>. This is the space where your Docker images are stored.";
        return $this->prompt->input($text, $helpText, null, true, ValidatorHelper::getDomainNameWithPortValidator()) ?? '';
    }

    /**
     * @return string
     */
    private function getProjectGroup(): string
    {
        $text = "\nYour project group";
        $helpText = "The group defined in the project path on <info>GitLab</info>. For instance, a project with URL https://git.yourcompany.com/foo/bar has <info>foo</info> as group name.";
        return $this->prompt->input($text, $helpText, null, true, ValidatorHelper::getAlphaWithAdditionalCharactersValidator(['-'])) ?? '';
    }

    /**
     * @return string
     */
    private function getProjectName(): string
    {
        $text = "\nYour project name";
        $helpText = "The name defined in the project path on <info>GitLab</info>. For instance, a project with URL https://git.yourcompany.com/foo/bar has <info>bar</info> as project name.";
        return $this->prompt->input($text, $helpText, null, true, ValidatorHelper::getAlphaWithAdditionalCharactersValidator(['-'])) ?? '';
    }
}
