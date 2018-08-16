<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;

class BuildDockerfileJob extends AbstractBuildJob
{
    /**
     * BuildDockerfileJob constructor.
     * @param string $envName
     * @param string $serviceName
     * @param string $registryDomainName
     * @param string $projectGroup
     * @param string $projectName
     * @param BranchesModel $branchesModel
     * @throws JobException
     */
    public function __construct(string $envName, string $serviceName, string $registryDomainName, string $projectGroup, string $projectName, BranchesModel $branchesModel)
    {
        parent::__construct($envName, $serviceName);

        $this->isVariableEnvironment = $branchesModel->isMultipleBranches();
        $tag = $this->isVariableEnvironment ? '#ENVIRONMENT#' : strtolower($branchesModel->getBranches()[0]);
        $this->dockerImageName = "$registryDomainName/$projectGroup/$projectName:$tag";

        $this->image = 'docker:git';
        $this->services[] = 'docker:dind';
        $this->variables = [
            'DOCKER_DRIVER' => 'overlay2',
            'REGISTRY_DOMAIN_NAME' => $registryDomainName,
            'PROJECT_GROUP' => $projectGroup,
            'PROJECT_NAME' => $projectName
        ];

        $scriptTag = $this->isVariableEnvironment ? '${CI_COMMIT_REF_SLUG}' : strtolower($branchesModel->getBranches()[0]);
        $this->script = [
            'docker login -u ${CI_DEPLOY_USER} -p ${CI_DEPLOY_PASSWORD} ${REGISTRY_DOMAIN_NAME}',
            'docker build -t ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:' . $scriptTag . ' .',
            'docker push ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:' . $scriptTag
        ];

        foreach ($branchesModel->getBranches() as $branch) {
            $this->addOnly($branch);
        }
        foreach ($branchesModel->getBranchesToIgnore() as $branch) {
            $this->addExcept($branch);
        }
    }
}
