<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\JobException;

final class DeployDockerComposeJob extends AbstractDeployJob
{
    /**
     * @param string $dockerComposeFilename
     * @param BaseGitLabCIContext $context
     * @param string $remoteIP
     * @param string $remoteUser
     * @param string $remoteBasePath
     * @param bool $isManual
     * @return DeployDockerComposeJob
     * @throws JobException
     */
    public static function newDeployOnRemoteServer(string $dockerComposeFilename, BaseGitLabCIContext $context, string $remoteIP, string $remoteUser, string $remoteBasePath, bool $isManual): self
    {
        $self = new self($context->getEnvironmentName());
        $self->image = 'kroniak/ssh-client:3.6';
        $self->variables = [
            'SSH_KNOWN_HOSTS' => 'You should put this value in your secrets CI variables!',
            'SSH_PRIVATE_KEY' => 'You should put this value in your secrets CI variables!',
            'DOCKER_COMPOSE_FILENAME' => $dockerComposeFilename,
            'REGISTRY_DOMAIN_NAME' => $context->getRegistryDomainName(),
            'REMOTE_IP' => $remoteIP,
            'REMOTE_USER' => $remoteUser,
            'REMOTE_BASE_PATH' => $remoteBasePath,
        ];
        $self->script = [
            'mkdir ~/.ssh',
            'echo "${SSH_KNOWN_HOSTS}" >> ~/.ssh/known_hosts',
            'chmod 644 ~/.ssh/known_hosts',
            'eval $(ssh-agent -s)',
            'ssh-add <(echo "${SSH_PRIVATE_KEY}"',
            'ssh ${REMOTE_USER}@${REMOTE_IP} "docker login -u ${CI_DEPLOY_USER} -p ${CI_DEPLOY_PASSWORD} ${REGISTRY_DOMAIN_NAME}"',
            'ssh ${REMOTE_USER}@${REMOTE_IP} "mkdir ${REMOTE_BASE_PATH}" || true',
            'ssh ${REMOTE_USER}@${REMOTE_IP} "cd ${REMOTE_BASE_PATH} && docker-compose down --rmi=all" || true',
            'scp ${DOCKER_COMPOSE_FILENAME} ${REMOTE_USER}@${REMOTE_IP}:${REMOTE_BASE_PATH}/docker-compose.yml',
            'ssh ${REMOTE_USER}@${REMOTE_IP} "cd ${REMOTE_BASE_PATH} && docker-compose up -d"'
        ];
        $branchesModel = $context->getBranchesModel();
        foreach ($branchesModel->getBranches() as $branch) {
            $self->addOnly($branch);
        }
        foreach ($branchesModel->getBranchesToIgnore() as $branch) {
            $self->addExcept($branch);
        }
        $self->environment = [
            'name' => 'review/$CI_COMMIT_REF_NAME',
            'url' => '# updates this with your environment URL',
        ];
        $self->manual = $isManual;
        return $self;
    }
}
