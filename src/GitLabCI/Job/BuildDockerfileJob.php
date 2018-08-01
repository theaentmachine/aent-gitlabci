<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Exception\JobException;

class BuildDockerfileJob extends AbstractBuildJob
{
    /**
     * BuildDockerfileJob constructor.
     * @param string $envName
     * @param string $serviceName
     * @param string $registryDomainName
     * @param string $projectGroup
     * @param string $projectName
     * @param string $branch
     * @throws JobException
     */
    public function __construct(string $envName, string $serviceName, string $registryDomainName, string $projectGroup, string $projectName, string $branch)
    {
        parent::__construct($envName, $serviceName);
        $this->isVariableEnvironment = false;
        $this->dockerImageName = "$registryDomainName/$projectGroup/$projectName:$branch";

        $this->image = 'docker:git';
        $this->services[] = 'docker:dind';
        $this->variables = [
            'DOCKER_DRIVER' => 'overlay2',
            'REGISTRY_DOMAIN_NAME' => $registryDomainName,
            'PROJECT_GROUP' => $projectGroup,
            'PROJECT_NAME' => $projectName
        ];
        $this->script = [
            'docker login -u ${CI_DEPLOY_USER} -p ${CI_DEPLOY_PASSWORD} ${REGISTRY_DOMAIN_NAME}',
            'docker build -t ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:${CI_COMMIT_REF_SLUG} .',
            'docker push ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:${CI_COMMIT_REF_SLUG}'
        ];

        $this->addOnly($branch);
    }
}
