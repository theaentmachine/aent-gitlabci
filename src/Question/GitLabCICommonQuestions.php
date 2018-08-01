<?php


namespace TheAentMachine\AentGitLabCI\Question;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Exception\ManifestException;
use TheAentMachine\Helper\AentHelper;
use TheAentMachine\Question\CommonValidators;

final class GitLabCICommonQuestions
{
    /** @var AentHelper */
    private $helper;

    /**
     * GitLabCICommonQuestions constructor.
     * @param AentHelper $helper
     */
    public function __construct(AentHelper $helper)
    {
        $this->helper = $helper;
    }

    public function askForRemoteIP(): string
    {
        $remoteIP = Manifest::getMetadata(Metadata::REMOTE_IP_KEY);

        if (empty($remoteIP)) {
            $remoteIP = $this->helper->question('Remote IP')
                ->setHelpText('The IP of the server where you want to deploy your stack.')
                ->compulsory()
                ->setValidator(CommonValidators::getIPv4Validator())
                ->ask();

            Manifest::addMetadata(Metadata::REMOTE_IP_KEY, $remoteIP);
        }

        return $remoteIP;
    }

    public function askForRemoteUser(): string
    {
        $remoteUser = Manifest::getMetadata(Metadata::REMOTE_USER_KEY);

        if (empty($remoteUser)) {
            $remoteUser = $this->helper->question('Remote user')
                ->setHelpText('The username of the user which will deploy over SSH your stack.')
                ->compulsory()
                ->setValidator(CommonValidators::getAlphaValidator(['_', '-'], 'User names can contain alphanumeric characters and "_", "-".'))
                ->ask();

            Manifest::addMetadata(Metadata::REMOTE_USER_KEY, $remoteUser);
        }

        return $remoteUser;
    }

    public function askForRemoteBasePath(): string
    {
        $remoteBasePath = Manifest::getMetadata(Metadata::REMOTE_BASE_PATH_KEY);

        if (empty($remoteBasePath)) {
            $remoteBasePath = $this->helper->question('Remote base path')
                ->setHelpText('The absolute path (without trailing "/") on the server where your stack will be deployed.')
                ->compulsory()
                ->setValidator(CommonValidators::getAbsolutePathValidator())
                ->ask();

            Manifest::addMetadata(Metadata::REMOTE_BASE_PATH_KEY, $remoteBasePath);
        }

        return $remoteBasePath;
    }


    /**
     * @param bool $askForMultipleBranches
     * @return BranchesModel
     * @throws JobException
     */
    public function askForBranches(bool $askForMultipleBranches): BranchesModel
    {
        try {
            $branchesModel = BranchesModel::newFromMetadata();
            return $branchesModel;
        } catch (ManifestException $e) {
            if (!$askForMultipleBranches) {
                return $this->askForBranch();
            }

            $multipleBranches = $this->helper->question('Do you want to deploy your stack on <info>multiple branches</info>?')
                ->compulsory()
                ->yesNoQuestion()
                ->ask();

            if ($multipleBranches) {
                $branch = 'foo';
                $branchesToIgnore = [];
                while (!empty($branch)) {
                    $branch = $this->helper->question('Git branch to ignore (leave empty to skip)')
                        ->setDefault('')
                        ->setValidator(CommonValidators::getAlphaValidator(['_', '.', '-'], 'branch names can contain alphanumeric characters and "_", ".", "-".'))
                        ->ask();

                    if (!empty($branch)) {
                        $branchesToIgnore[] = $branch;
                    }
                }

                $branchesModel = new BranchesModel(true, null, $branchesToIgnore);
                $branchesModel->feedMetadata();

                return $branchesModel;
            }

            return $this->askForBranch();
        }
    }

    /**
     * @return BranchesModel
     * @throws JobException
     */
    private function askForBranch(): BranchesModel
    {
        $branch = $this->helper->question('Git branch')
            ->compulsory()
            ->setValidator(CommonValidators::getAlphaValidator(['_', '.', '-'], 'branch names can contain alphanumeric characters and "_", ".", "-".'))
            ->ask();

        $branchesModel = new BranchesModel(false, $branch);
        $branchesModel->feedMetadata();

        return $branchesModel;
    }

    public function askForManual(): bool
    {
        $manual = Manifest::getMetadata(Metadata::IS_MANUAL_KEY);
        if (!empty($manual)) {
            return boolval($manual);
        }

        $manual = $this->helper->question('Do you want to deploy your stack <info>manually</info>?')
            ->compulsory()
            ->yesNoQuestion()
            ->ask();
        $manual = $manual ? 'true' : 'false';

        Manifest::addMetadata(Metadata::IS_MANUAL_KEY, $manual);

        return $manual === 'true';
    }
}
