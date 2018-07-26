<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

final class DeployDockerComposeJob extends AbstractDeployJob
{
    public function __construct(string $envName, string $registryDomainName, string $dockerComposeFileName, string $remoteIP, string $remoteUser, string $remotePath)
    {
        parent::__construct($envName);

        $this->image = 'kroniak/ssh-client:3.6';
        $this->script = [
            'mkdir ~/.ssh',
            'echo "$SSH_KNOWN_HOSTS" >> ~/.ssh/known_hosts',
            'chmod 644 ~/.ssh/known_hosts',
            'eval $(ssh-agent -s)',
            'ssh-add <(echo "$SSH_PRIVATE_KEY"',
            "ssh $remoteUser@$remoteIP \"docker login -u \$CI_DEPLOY_USER -p \$CI_DEPLOY_PASSWORD $registryDomainName\"",
            "ssh $remoteUser@$remoteIP \"cd $remotePath && docker-compose down --rmi=all\" || true",
            "scp $dockerComposeFileName $remoteUser@$remoteIP:$remotePath/docker-compose.yml",
            "ssh $remoteUser@$remoteIP \"cd $remotePath && docker-compose up -d\"",
        ];
        $this->manual = true;
    }
}