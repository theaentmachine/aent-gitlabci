<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\Exception\PayloadException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\CleanupKubernetesJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\DeployKubernetesJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\AentGitLabCI\Question\GitLabCICommonQuestions;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractEventCommand;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Exception\MissingEnvironmentVariableException;

final class NewDeployKubernetesJobEventCommand extends AbstractEventCommand
{
    /** @var string */
    private $envName;

    /** @var string */
    private $registryDomainName;

    /** @var string */
    private $k8sDirName;

    protected function getEventName(): string
    {
        return CommonEvents::NEW_DEPLOY_KUBERNETES_JOB_EVENT;
    }

    /**
     * @param null|string $k8sDirName
     * @return null|string
     * @throws GitLabCIFileException
     * @throws JobException
     * @throws ManifestException
     * @throws MissingEnvironmentVariableException
     * @throws PayloadException
     */
    protected function executeEvent(?string $k8sDirName): ?string
    {
        $aentHelper = $this->getAentHelper();

        $aentHelper->title('GitLab CI: adding a deploy stage');

        if (null === $k8sDirName) {
            throw PayloadException::missingKubernetesPathname();
        }

        $this->envName = Manifest::mustGetMetadata(CommonMetadata::ENV_NAME_KEY);
        $this->registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);
        $this->k8sDirName = $k8sDirName;

        $this->output->writeln("🦊×☸️ Kubernetes folder: <info>$this->k8sDirName</info>");
        $aentHelper->spacer();

        $deployJob = $this->askForDeployType();


        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addDeploy($deployJob);
        switch (Manifest::mustGetMetadata(Metadata::DEPLOY_TYPE_KEY)) {
            case Metadata::DEPLOY_TYPE_GCLOUD:
                $cleanUpJob = $this->createCleanupOnGCloud();
                $file->addCleanUp($cleanUpJob);
                break;
            case Metadata::DEPLOY_TYPE_RANCHER:
                $cleanUpJob = $this->createCleanupForRancher();
                $file->addCleanUp($cleanUpJob);
                break;
        }

        $this->output->writeln('🦊 <info>' . GitLabCIFile::DEFAULT_FILENAME . '</info> has been successfully updated!');

        return null;
    }

    /**
     * @return DeployKubernetesJob
     * @throws JobException
     * @throws ManifestException
     */
    private function askForDeployType(): DeployKubernetesJob
    {
        $deployType = Manifest::getMetadata(Metadata::DEPLOY_TYPE_KEY);

        if (null === $deployType) {
            $deployType = $this->getAentHelper()
                ->choiceQuestion('Select on which provider you want to deploy your stack', [
                    Metadata::DEPLOY_TYPE_GCLOUD,
                    Metadata::DEPLOY_TYPE_RANCHER
                ])
                ->ask();
        }

        switch ($deployType) {
            case Metadata::DEPLOY_TYPE_GCLOUD:
                return $this->createDeployOnGCloud();
            case Metadata::DEPLOY_TYPE_RANCHER:
                return $this->createDeployOnRancher();
            default:
                throw JobException::unknownDeployType($deployType);
        }
    }

    /**
     * @return DeployKubernetesJob
     * @throws JobException
     * @throws ManifestException
     */
    private function createDeployOnGCloud(): DeployKubernetesJob
    {
        Manifest::addMetadata(Metadata::DEPLOY_TYPE_KEY, Metadata::DEPLOY_TYPE_GCLOUD);

        $gitlabCICommonQuestions = new GitLabCICommonQuestions($this->getAentHelper());
        $branchesModel = BranchesModel::newFromMetadata();
        $isManual = $gitlabCICommonQuestions->askForManual();

        return DeployKubernetesJob::newDeployOnGCloud(
            $this->envName,
            $this->k8sDirName,
            $branchesModel,
            $isManual
        );
    }

    /**
     * @return CleanupKubernetesJob
     * @throws JobException
     * @throws ManifestException
     */
    private function createCleanupOnGCloud(): CleanupKubernetesJob
    {
        $gitlabCICommonQuestions = new GitLabCICommonQuestions($this->getAentHelper());
        $registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);
        $projectGroup = Manifest::mustGetMetadata(Metadata::PROJECT_GROUP_KEY);
        $projectName = Manifest::mustGetMetadata(Metadata::PROJECT_NAME_KEY);
        $branchesModel = BranchesModel::newFromMetadata();
        $isManual = $gitlabCICommonQuestions->askForManual();

        return CleanupKubernetesJob::newCleanupForGCloud(
            $this->envName,
            $registryDomainName,
            $projectGroup,
            $projectName,
            $branchesModel,
            $isManual
        );
    }

    /**
     * @return DeployKubernetesJob
     * @throws JobException
     * @throws ManifestException
     */
    private function createDeployOnRancher(): DeployKubernetesJob
    {
        Manifest::addMetadata(Metadata::DEPLOY_TYPE_KEY, Metadata::DEPLOY_TYPE_RANCHER);

        $gitlabCICommonQuestions = new GitLabCICommonQuestions($this->getAentHelper());
        $branchesModel = BranchesModel::newFromMetadata();
        $isManual = $gitlabCICommonQuestions->askForManual();

        return DeployKubernetesJob::newDeployOnRancher(
            $this->envName,
            $this->k8sDirName,
            $branchesModel,
            $isManual
        );
    }

    /**
     * @return CleanupKubernetesJob
     * @throws JobException
     * @throws ManifestException
     */
    private function createCleanupForRancher(): CleanupKubernetesJob
    {
        $gitlabCICommonQuestions = new GitLabCICommonQuestions($this->getAentHelper());
        $registryDomainName = Manifest::mustGetMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY);
        $projectGroup = Manifest::mustGetMetadata(Metadata::PROJECT_GROUP_KEY);
        $projectName = Manifest::mustGetMetadata(Metadata::PROJECT_NAME_KEY);
        $branchesModel = BranchesModel::newFromMetadata();
        $isManual = $gitlabCICommonQuestions->askForManual();

        return CleanupKubernetesJob::newCleanupForRancher(
            $this->envName,
            $registryDomainName,
            $projectGroup,
            $projectName,
            $branchesModel,
            $isManual
        );
    }
}
