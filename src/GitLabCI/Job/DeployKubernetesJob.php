<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;

final class DeployKubernetesJob extends AbstractDeployJob
{
    /**
     * @param string $k8sDirName
     * @param BaseGitLabCIContext $context
     * @param BranchesModel $branchesModel
     * @param string $cleanupJobIdentifier
     * @param bool $isManual
     * @return DeployKubernetesJob
     * @throws JobException
     */
    public static function newDeployOnGCloud(string $k8sDirName, BaseGitLabCIContext $context, BranchesModel $branchesModel, string $cleanupJobIdentifier, bool $isManual): self
    {
        $self = new self($context->getEnvironmentName());
        $self->image = 'thecodingmachine/k8s-gitlabci:latest';
        $self->variables = [
            'GCLOUD_SERVICE_KEY_BASE64' => 'You should put this value in your secrets CI variables!',
            'GCLOUD_PROJECT' => 'You should put this value in your secrets CI variables!',
            'GKE_CLUSTER' => 'You should put this value in your secrets CI variables!',
            'ZONE' => 'You should put this value in your secrets CI variables!',
            'KUBECONFIG' => '/root/.kube/config',
            'K8S_DIRNAME' => $k8sDirName,
        ];
        $self->script = [
            'echo $GCLOUD_SERVICE_KEY_BASE64 | base64 -d > /secret.json',
            'gcloud auth activate-service-account --key-file /secret.json',
            'gcloud config set project $GCLOUD_PROJECT',
            'gcloud container clusters get-credentials $GKE_CLUSTER --zone $ZONE --project $GCLOUD_PROJECT',
            'kubectl create namespace ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} || true',
            'kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} delete all --all',
            'cd ${K8S_DIRNAME}',
            'for template_file in $(find . -type f -name "*.template"); do sed -e "s/#ENVIRONMENT#/${CI_COMMIT_REF_SLUG}/g" $template_file > ${template_file::-9}; done',
            'for yml_file in $(find . -type f -name "*.yml" -or -name "*.yaml"); do kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} apply -f ${yml_file}; done'
        ];
        foreach ($branchesModel->getBranches() as $branch) {
            $self->addOnly($branch);
        }
        foreach ($branchesModel->getBranchesToIgnore() as $branch) {
            $self->addExcept($branch);
        }
        $self->environment = [
            'name' => 'review/$CI_COMMIT_REF_NAME',
            'url' => '# updates this with your environment URL',
            'on_stop' => $cleanupJobIdentifier
        ];
        $self->manual = $isManual;
        return $self;
    }

    /**
     * @param string $k8sDirName
     * @param BaseGitLabCIContext $context
     * @param BranchesModel $branchesModel
     * @param string $cleanupJobIdentifier
     * @param bool $isManual
     * @return DeployKubernetesJob
     * @throws JobException
     */
    public static function newDeployOnRancher(string $k8sDirName, BaseGitLabCIContext $context, BranchesModel $branchesModel, string $cleanupJobIdentifier, bool $isManual): self
    {
        $self = new self($context->getEnvironmentName());
        $self->image = 'thecodingmachine/k8s-gitlabci:latest';
        $self->variables = [
            'KUBECONFIG' => '/root/.kube/config',
            'K8S_DIRNAME' => $k8sDirName,
        ];
        $self->script = [
            'mkdir ~/.kube',
            'echo "$KUBE_CONFIG" > ~/.kube/config',
            'kubectl create namespace ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} || true',
            'kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} delete all --all',
            'cd ${K8S_DIRNAME}',
            // TODO: add a docker-registry secret?
            'for template_file in $(find . -type f -name "*.template"); do sed -e "s/#ENVIRONMENT#/${CI_COMMIT_REF_SLUG}/g" $template_file > ${template_file::-9}; done',
            'for yml_file in $(find . -type f -name "*.yml" -or -name "*.yaml"); do kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} apply -f ${yml_file}; done'
        ];
        foreach ($branchesModel->getBranches() as $branch) {
            $self->addOnly($branch);
        }
        foreach ($branchesModel->getBranchesToIgnore() as $branch) {
            $self->addExcept($branch);
        }
        $self->environment = [
            'name' => 'review/$CI_COMMIT_REF_NAME',
            'url' => '# updates this with your environment URL',
            'on_stop' => $cleanupJobIdentifier
        ];
        $self->manual = $isManual;
        return $self;
    }
}
