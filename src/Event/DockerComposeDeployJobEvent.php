<?php

namespace TheAentMachine\AentGitLabCI\Event;

use Safe\Exceptions\FilesystemException;
use TheAentMachine\Aent\Context\Context;
use TheAentMachine\Aent\Event\CI\AbstractCIDockerComposeDeployJobEvent;
use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\DeployDockerComposeJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Prompt\Helper\ValidatorHelper;

final class DockerComposeDeployJobEvent extends AbstractCIDockerComposeDeployJobEvent
{
    /**
     * @param string $dockerComposeFilename
     * @return void
     * @throws JobException
     * @throws GitLabCIFileException
     * @throws MissingEnvironmentVariableException
     * @throws FilesystemException
     */
    protected function addDeployJob(string $dockerComposeFilename): void
    {
        $this->output->writeln("\n🦊 Currently, we only support a deploy on a remote server and on a single branch when using Docker Compose as orchestrator!");
        $this->output->writeln("Important: If you're using a dot env file, make sure to create it on your remote server!");
        $branchesModel = $this->getBranch();
        $remoteIP = $this->getRemoteIP();
        $remoteUser = $this->getRemoteUser();
        $remoteBasePath = $this->getRemoteBasePath();
        $isManual = $this->deployManually();
        $this->prompt->printAltBlock("GitLab: adding deploy job...");
        $context = new BaseGitLabCIContext();
        $context->setBranchesModel($branchesModel);
        $context->toMetadata();
        $job = DeployDockerComposeJob::newDeployOnRemoteServer($dockerComposeFilename, $context, $remoteIP, $remoteUser, $remoteBasePath, $isManual);
        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addDeploy($job);
    }

    /**
     * @return BranchesModel
     * @throws JobException
     */
    private function getBranch(): BranchesModel
    {
        $context = Context::fromMetadata();
        $text = "\nYour branch";
        $helpText = "The branch for which GitLab will deploy your stack and create images from Dockerfiles.";
        $default = null;
        if ($context->isTest()) {
            $default = 'testing';
        } elseif ($context->isProduction()) {
            $default = 'master';
        }
        $branchName = $this->prompt->input($text, $helpText, $default, true, ValidatorHelper::getAlphaWithAdditionalCharactersValidator(['_', '.', '-'])) ?? '';
        return new BranchesModel([$branchName]);
    }

    /**
     * @return string
     */
    private function getRemoteIP(): string
    {
        $text = "\nIP of your remote server";
        $helpText = "The IP of the server where you want to deploy your stack.";
        return $this->prompt->input($text, $helpText, null, true, ValidatorHelper::getIPv4Validator()) ?? '';
    }

    /**
     * @return string
     */
    private function getRemoteUser(): string
    {
        $text = "\nDeploy user";
        $helpText = "The username of the user which will deploy over SSH your stack.";
        return $this->prompt->input($text, $helpText, null, true, ValidatorHelper::getAlphaWithAdditionalCharactersValidator(['_', '-'])) ?? '';
    }

    /**
     * @return string
     */
    private function getRemoteBasePath(): string
    {
        $text = "\nAbsolute path on the server";
        $helpText = "The absolute path (without trailing \"/\") on the server where your stack will be deployed.";
        return $this->prompt->input($text, $helpText, null, true, ValidatorHelper::getAbsolutePathValidator()) ?? '';
    }

    /**
     * @return bool
     */
    private function deployManually(): bool
    {
        $text = "\nDo you want to deploy your stack <info>manually</info>?";
        return $this->prompt->confirm($text, null, null, true);
    }
}
