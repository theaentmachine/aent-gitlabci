<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\JobException;

final class BuildDockerfileJob extends AbstractBuildJob
{
    /**
     * BuildDockerfileJob constructor.
     * @param string $serviceName
     * @param string $dockerfileName
     * @param BaseGitLabCIContext $context
     * @throws JobException
     */
    public function __construct(string $serviceName, string $dockerfileName, BaseGitLabCIContext $context)
    {
        parent::__construct($context->getEnvironmentName(), $serviceName);
        $branchesModel = $context->getBranchesModel();
        $registryDomainName = $context->getRegistryDomainName();
        $projectGroup = $context->getProjectGroup();
        $projectName = $context->getProjectName();
        $this->isSingleBranch = $branchesModel->isSingleBranch();
        $tag = $this->isSingleBranch ? strtolower($branchesModel->getBranches()[0]) : '#ENVIRONMENT#';
        $this->dockerImageName = "$registryDomainName/$projectGroup/$projectName:$tag";
        $this->image = 'docker:git';
        $this->services[] = 'docker:dind';
        $this->variables = [
            'DOCKER_DRIVER' => 'overlay2',
            'REGISTRY_DOMAIN_NAME' => $registryDomainName,
            'PROJECT_GROUP' => $projectGroup,
            'PROJECT_NAME' => $projectName,
            'DOCKERFILE_NAME' => $dockerfileName
        ];
        $scriptTag = $this->isSingleBranch ? strtolower($branchesModel->getBranches()[0]) : '${CI_COMMIT_REF_SLUG}';
        $this->script = [
            'docker login -u ${CI_REGISTRY_USER} -p ${CI_REGISTRY_PASSWORD} ${REGISTRY_DOMAIN_NAME}',
            'docker build -t ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:' . $scriptTag . ' -f ${DOCKERFILE_NAME} .',
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
