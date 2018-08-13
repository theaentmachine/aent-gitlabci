<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Exception\JobException;

final class DeployKubernetesJob extends AbstractDeployJob
{
    /**
     * @param string $identifier
     * @param string $k8sBasePath
     * @param bool $isManual
     * @return DeployKubernetesJob
     * @throws JobException
     */
    public static function newDeployOnGCloud(string $identifier, string $k8sBasePath, bool $isManual): self
    {
        $self = new self($identifier);

        $self->image = 'claranet/gcloud-kubectl-docker:1.2.0';
        $self->variables = [
            'KUBECONFIG' => '/root/.kube/config',
            'GCLOUD_SERVICE_KEY_BASE64' => 'You should put this value in your secrets CI variables!',
            'GCLOUD_PROJECT' => 'You should put this value in your secrets CI variables!',
            '$ZONE' => 'You should put this value in your secrets CI variables!',
            'GKE_CLUSTER' => 'You should put this value in your secrets CI variables!',
            'K8S_BASE_PATH' => $k8sBasePath,
        ];
        $self->script = [
            'echo $GCLOUD_SERVICE_KEY_BASE64 | base64 -d > /secret.json',
            'gcloud auth activate-service-account --key-file /secret.json',
            'gcloud config set project $GCLOUD_PROJECT',
            'gcloud container clusters get-credentials $GKE_CLUSTER --zone $ZONE --project $GCLOUD_PROJECT',
            'chmod +x /kubectl',
            '/kubectl create namespace ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} || true',
            '/kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} delete all --all',
            'cd ${K8S_BASE_PATH}', '# Looping through directories and applying k8s config files',
            'for template_file in $(find . -type f -name "*.templates"); do sed -e "s/#ENVIRONMENT#/${CI_COMMIT_REF_SLUG}/g" $template_file > ${template_file::-9}; done',
            'for yml_file in $(find . -type f -name "*.yml" -or -name "*.yaml"); do /kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} apply -f yml_file; done',
            'sed -e "s/#ENVIRONMENT#/${CI_COMMIT_REF_SLUG}/g" web.yaml.template > web.yaml',
        ];

        $self->addOnly('branches');
        $self->addExcept('master');
        $self->manual = $isManual;

        return $self;
    }
}
