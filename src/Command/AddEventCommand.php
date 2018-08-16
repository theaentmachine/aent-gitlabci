<?php

namespace TheAentMachine\AentGitLabCI\Command;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\GitLabCIFileException;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\GitLabCIFile;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Aenthill\CommonMetadata;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Command\AbstractEventCommand;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Exception\MissingEnvironmentVariableException;
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
     * @throws MissingEnvironmentVariableException
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

        if (null === Manifest::getMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY)) {
            $registryDomainName = $this->askForRegistryDomainName();
            Manifest::addMetadata(Metadata::REGISTRY_DOMAIN_NAME_KEY, $registryDomainName);
        }

        if (null === Manifest::getMetadata(Metadata::PROJECT_GROUP_KEY)) {
            $projectGroup = $this->askForProjectGroup();
            Manifest::addMetadata(Metadata::PROJECT_GROUP_KEY, $projectGroup);
        }

        if (null === Manifest::getMetadata(Metadata::PROJECT_NAME_KEY)) {
            $projectName = $this->askForProjectName();
            Manifest::addMetadata(Metadata::PROJECT_NAME_KEY, $projectName);
        }

        if (null === Manifest::getMetadata(CommonMetadata::SINGLE_ENVIRONMENT_KEY)) {
            $branchesModel = $this->askForBranches((bool)$payload);
            Manifest::addMetadata(CommonMetadata::SINGLE_ENVIRONMENT_KEY, (string)!$branchesModel->isSingleBranch());
        }

        return Manifest::getMetadata(CommonMetadata::SINGLE_ENVIRONMENT_KEY);
    }

    private function askForRegistryDomainName(): string
    {
        return $this->getAentHelper()->question('Registry domain name')
            ->setHelpText('The domain name of the Docker Container Registry integrated with your Git repository on your GitLab. This is the space where your Docker images are stored.')
            ->compulsory()
            ->setValidator(CommonValidators::getDomainNameWithPortValidator())
            ->ask();
    }

    private function askForProjectGroup(): string
    {
        return $this->getAentHelper()->question('Project group')
            ->setHelpText('The group defined in the project path on GitLab. For example: for the project with URL "https://git.yourcompany.com/foo/bar", "foo" is the group name.')
            ->compulsory()
            ->setValidator(CommonValidators::getAlphaValidator(['-']))
            ->ask();
    }

    private function askForProjectName(): string
    {
        return $this->getAentHelper()->question('Project name')
            ->setHelpText('The project name defined in the project path on GitLab. For example: for the project with URL "https://git.yourcompany.com/foo/bar", "bar" is the project name.')
            ->compulsory()
            ->setValidator(CommonValidators::getAlphaValidator(['-']))
            ->ask();
    }

    private function askForBranches(bool $forSingleEnvironment = false): BranchesModel
    {
        try {
            $branchesModel = BranchesModel::newFromMetadata();
            return $branchesModel;
        } catch (ManifestException | JobException $e) {
            if ($forSingleEnvironment) {
                $branch = $this->askForBranch(true);
                return new BranchesModel([$branch], []);
            }
            $singleBranch = 'On one single branch';
            $allBranches = 'On all branches';
            $customBranches = 'Choose custom branches';
            $choices = [$singleBranch, $allBranches, $customBranches];
            $strategy = $this->getAentHelper()->choiceQuestion('Which deployment strategy to you want to apply?', $choices)
                ->ask();

            $branches = [];
            $branchesToIgnore = [];
            switch ($strategy) {
                case $singleBranch:
                    $branches[] = $this->askForBranch(true);
                    break;
                case $allBranches:
                    $branches[] = 'branches';
                    break;
                case $customBranches:
                    $branches[] = $this->askForBranch(true);
                    do {
                        $anotherBranch = $this->askForBranch(false);
                        if (!empty($anotherBranch)) {
                            $branches[] = $anotherBranch;
                        }
                    } while (!empty($anotherBranch));
                    break;
            }

            if ($strategy !== $singleBranch) {
                do {
                    $branchToIgnore = $this->getAentHelper()->question('Git branch to ignore (leave empty to skip)')
                        ->setDefault('')
                        ->setValidator(CommonValidators::getAlphaValidator(['_', '.', '-'], 'branch names can contain alphanumeric characters and "_", ".", "-".'))
                        ->ask();
                    if (!empty($branchToIgnore)) {
                        $branchesToIgnore[] = $branchToIgnore;
                    }
                } while (!empty($branchToIgnore));
            }

            $branchesModel = new BranchesModel($branches, $branchesToIgnore);
            $branchesModel->feedMetadata();
            return $branchesModel;
        }
    }

    private function askForBranch(bool $compulsory = true): string
    {
        $questionText = $compulsory ? 'Git branch' : 'Git branch (leave empty to skip)';
        $question = $this->getAentHelper()->question($questionText)
            ->setDefault('')
            ->setValidator(CommonValidators::getAlphaValidator(['_', '.', '-'], 'branch names can contain alphanumeric characters and "_", ".", "-".'));
        if ($compulsory) {
            $question->compulsory();
        }
        return $question->ask();
    }
}
