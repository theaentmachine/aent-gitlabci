<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;

final class DeployDockerComposeJob extends AbstractDeployJob
{

    /**
     * @param string $identifier
     * @param string $registryDomainName
     * @param string $dockerComposeFilename
     * @param string $remoteIP
     * @param string $remoteUser
     * @param string $remoteBasePath
     * @param BranchesModel $branches
     * @param bool $isManual
     * @return DeployDockerComposeJob
     * @throws JobException
     */
    public static function newDeployOnRemoteServer(string $identifier, string $registryDomainName, string $dockerComposeFilename, string $remoteIP, string $remoteUser, string $remoteBasePath, BranchesModel $branches, bool $isManual): self
    {
        $self = new self($identifier);

        $self->image = 'kroniak/ssh-client:3.6';
        $self->variables = [
            'SSH_KNOWN_HOSTS' => 'You should put this value in your secrets CI variables!',
            'SSH_PRIVATE_KEY' => 'You should put this value in your secrets CI variables!',
            'DOCKER_COMPOSE_FILENAME' => $dockerComposeFilename,
            'REGISTRY_DOMAIN_NAME' => $registryDomainName,
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

        $branch = $branches->getBranch();
        if (empty($branch)) {
            throw JobException::branchIsNull();
        }

        $self->addOnly($branch);
        $self->manual = $isManual;

        return $self;
    }
}
