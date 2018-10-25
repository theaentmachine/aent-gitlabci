<?php

namespace TheAentMachine\AentGitLabCI\Event;

use Safe\Exceptions\FilesystemException;
use TheAentMachine\Aent\Event\CI\AbstractCIKubernetesDeployJobEvent;
use TheAentMachine\Aent\K8SProvider\Provider;
use TheAentMachine\Aent\Payload\CI\KubernetesReplyDeployJobPayload;
use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\CleanupKubernetesJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\DeployKubernetesJob;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
use TheAentMachine\Prompt\Helper\ValidatorHelper;

final class KubernetesDeployJobEvent extends AbstractCIKubernetesDeployJobEvent
{
    /** @var string[] */
    private $deployBranches = [];

    /** @var string[] */
    private $ignoredBranches = [];

    /**
     * @param string $directoryName
     * @param Provider $provider
     * @return KubernetesReplyDeployJobPayload
     * @throws FilesystemException
     * @throws GitLabCIFileException
     * @throws JobException
     * @throws MissingEnvironmentVariableException
     */
    protected function addDeployJob(string $directoryName, Provider $provider): KubernetesReplyDeployJobPayload
    {
        $this->output->writeln("\n🦊 Currently, we only support a deploy on branches when using Kubernetes as orchestrator!");
        $branchesModel = $this->getBranches();
        $isManual = $this->deployManually();
        $this->prompt->printAltBlock("GitLab: adding deploy job...");
        $context = new BaseGitLabCIContext();
        $context->setBranchesModel($branchesModel);
        $context->toMetadata();
        if ($provider->getName() === Provider::GOOGLE_CLOUD) {
            $job = DeployKubernetesJob::newDeployOnGCloud($directoryName, $context, $branchesModel, $isManual);
        } else {
            $job = DeployKubernetesJob::newDeployOnRancher($directoryName, $context, $branchesModel, $isManual);
        }
        $file = new GitLabCIFile();
        $file->findOrCreate();
        $file->addDeploy($job);
        $this->prompt->printAltBlock("GitLab: adding cleanup job...");
        if ($provider->getName() === Provider::GOOGLE_CLOUD) {
            $job = CleanupKubernetesJob::newCleanupForGCloud($context, $branchesModel, $isManual);
        } else {
            $job = CleanupKubernetesJob::newCleanupForRancher($context, $branchesModel, $isManual);
        }
        $file->addCleanUp($job);
        return new KubernetesReplyDeployJobPayload(!$branchesModel->isSingleBranch());
    }

    /**
     * @return BranchesModel
     * @throws JobException
     */
    private function getBranches(): BranchesModel
    {
        $text = "\nBranch for deploy (keep empty if you don't want to use a specific branch)";
        $helpText = "A branch for which GitLab will deploy your stack and create images from Dockerfiles.";
        $branchName = $this->getDeployBranch($text, $helpText);
        if (!empty($branchName)) {
            $this->deployBranches[] = $branchName;
            $text = $text = "\nBranch (keep empty to skip)";
            do {
                $branchName = $this->getDeployBranch($text, $helpText);
                if (!empty($branchName)) {
                    $this->deployBranches[] = $branchName;
                }
            } while (!empty($branchName));
        }
        $text = "\nBranch to ignore (keep empty to skip)";
        $helpText = "A branch for which GitLab will NOT deploy your stack and create images from Dockerfiles.";
        $branchName = $this->getIgnoredBranch($text, $helpText);
        if (!empty($branchName)) {
            $this->ignoredBranches[] = $branchName;
            do {
                $branchName = $this->getIgnoredBranch($text, $helpText);
                if (!empty($branchName)) {
                    $this->ignoredBranches[] = $branchName;
                }
            } while (!empty($branchName));
        }
        if (empty($this->deployBranches)) {
            $this->deployBranches = [ 'branches' ];
        }
        return new BranchesModel($this->deployBranches, $this->ignoredBranches);
    }

    /**
     * @param string $text
     * @param string $helpText
     * @return null|string
     */
    private function getDeployBranch(string $text, string $helpText): ?string
    {
        $validator = ValidatorHelper::merge(
            ValidatorHelper::getFuncShouldNotReturnTrueValidator([$this, 'doesDeployBranchExist'], 'Deploy branch "%s" does already exist!'),
            ValidatorHelper::getAlphaWithAdditionalCharactersValidator(['_', '.', '-'])
        );
        return $this->prompt->input($text, $helpText, null, false, $validator);
    }

    /**
     * @param string $text
     * @param string $helpText
     * @return null|string
     */
    private function getIgnoredBranch(string $text, string $helpText): ?string
    {
        $validator = ValidatorHelper::merge(
            ValidatorHelper::getFuncShouldNotReturnTrueValidator([$this, 'doesIgnoredBranchExist'], 'Branch "%s" is already ignored or used for deploy!'),
            ValidatorHelper::getAlphaWithAdditionalCharactersValidator(['_', '.', '-'])
        );
        return $this->prompt->input($text, $helpText, null, false, $validator);
    }

    /**
     * @param string|null $branchName
     * @return bool
     */
    public function doesDeployBranchExist(?string $branchName): bool
    {
        if (empty($branchName)) {
            return false;
        }
        foreach ($this->deployBranches as $deployBranchName) {
            if ($deployBranchName === $branchName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string|null $branchName
     * @return bool
     */
    public function doesIgnoredBranchExist(?string $branchName): bool
    {
        $resp = $this->doesDeployBranchExist($branchName);
        if ($resp) {
            return $resp;
        }
        foreach ($this->ignoredBranches as $ignoredBranchName) {
            if ($ignoredBranchName === $branchName) {
                return true;
            }
        }
        return false;
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
