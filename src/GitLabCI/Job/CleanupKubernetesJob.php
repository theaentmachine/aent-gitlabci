<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Exception\JobException;

final class CleanupKubernetesJob extends AbstractCleanupJob
{
    /**
     * @param string $identifier
     * @param string $projectGroup
     * @param string $projectName
     * @param bool $isManual
     * @return CleanupKubernetesJob
     * @throws JobException
     */
    public static function newCleanup(string $identifier, string $projectGroup, string $projectName, bool $isManual): self
    {
        $self = new self($identifier);

        $self->image = 'thecodingmachine/gitlab-registry-cleaner:latest';
        $self->variables = [
            'KUBECONFIG' => '/root/.kube/config',
            'GCLOUD_SERVICE_KEY_BASE64' => 'You should put this value in your secrets CI variables!',
            'GCLOUD_PROJECT' => 'You should put this value in your secrets CI variables!',
            '$ZONE' => 'You should put this value in your secrets CI variables!',
            'GKE_CLUSTER' => 'You should put this value in your secrets CI variables!',
            'PROJECT_GROUP' => $projectGroup,
            'PROJECT_NAME' => $projectName,

        ];
        $self->script = [
            'echo $GCLOUD_SERVICE_KEY_BASE64 | base64 -d > /secret.json',
            'gcloud auth activate-service-account --key-file /secret.json',
            'gcloud config set project $GCLOUD_PROJECT',
            'gcloud container clusters get-credentials $GKE_CLUSTER --zone $ZONE --project $GCLOUD_PROJECT',
            'chmod +x /kubectl',
            '/delete_image.sh ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:${CI_COMMIT_REF_SLUG}',
            'kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} delete all --all',
            'kubectl delete namespace ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG}',
        ];

        $self->addOnly('branches');
        $self->addExcept('master');
        $self->manual = $isManual;

        return $self;
    }
}
