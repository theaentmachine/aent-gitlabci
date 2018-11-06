<?php

namespace TheAentMachine\AentGitLabCI\GitLabCI\Job;

use TheAentMachine\AentGitLabCI\Context\BaseGitLabCIContext;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;

final class CleanupKubernetesJob extends AbstractCleanupJob
{
    /**
     * @param BaseGitLabCIContext $context
     * @param BranchesModel $branchesModel
     * @param bool $isManual
     * @return CleanupKubernetesJob
     * @throws JobException
     */
    public static function newCleanupForGCloud(BaseGitLabCIContext $context, BranchesModel $branchesModel, bool $isManual): self
    {
        $self = new self($context->getEnvironmentName());
        $self->image = 'thecodingmachine/k8s-gitlabci:latest';
        $self->variables = [
            'GCLOUD_SERVICE_KEY_BASE64' => 'You should put this value in your secrets CI variables!',
            'GCLOUD_PROJECT' => 'You should put this value in your secrets CI variables!',
            'GKE_CLUSTER' => 'You should put this value in your secrets CI variables!',
            'ZONE' => 'You should put this value in your secrets CI variables!',
            'KUBECONFIG' => '/root/.kube/config',
            'REGISTRY_DOMAIN_NAME' => $context->getRegistryDomainName(),
            'PROJECT_GROUP' => $context->getProjectGroup(),
            'PROJECT_NAME' => $context->getProjectName()
        ];
        $scriptTag = $branchesModel->isSingleBranch() ? strtolower($branchesModel->getBranches()[0]) : '${CI_COMMIT_REF_SLUG}';
        $self->script = [
            '/delete_image.sh ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:' . $scriptTag,
            'echo $GCLOUD_SERVICE_KEY_BASE64 | base64 -d > /secret.json',
            'gcloud auth activate-service-account --key-file /secret.json',
            'gcloud config set project $GCLOUD_PROJECT',
            'gcloud container clusters get-credentials $GKE_CLUSTER --zone $ZONE --project $GCLOUD_PROJECT',
            'kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} delete all --all',
            'kubectl delete namespace ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG}',
        ];
        foreach ($branchesModel->getBranches() as $branch) {
            $self->addOnly($branch);
        }
        foreach ($branchesModel->getBranchesToIgnore() as $branch) {
            $self->addExcept($branch);
        }
        $self->environment = [
            'name' => 'review/$CI_COMMIT_REF_NAME',
            'action' => 'stop',
        ];
        $self->manual = true;
        return $self;
    }

    /**
     * @param BaseGitLabCIContext $context
     * @param BranchesModel $branchesModel
     * @param bool $isManual
     * @return CleanupKubernetesJob
     * @throws JobException
     */
    public static function newCleanupForRancher(BaseGitLabCIContext $context, BranchesModel $branchesModel, bool $isManual): self
    {
        $self = new self($context->getEnvironmentName());
        $self->image = 'thecodingmachine/gitlab-registry-cleaner:latest';
        $self->variables = [
            'KUBECONFIG' => '/root/.kube/config',
            'REGISTRY_DOMAIN_NAME' => $context->getRegistryDomainName(),
            'PROJECT_GROUP' => $context->getProjectGroup(),
            'PROJECT_NAME' => $context->getProjectName()
        ];
        $scriptTag = $branchesModel->isSingleBranch() ? strtolower($branchesModel->getBranches()[0]) : '${CI_COMMIT_REF_SLUG}';
        $self->script = [
            'mkdir ~/.kube',
            'echo "$KUBE_CONFIG" > ~/.kube/config',
            'kubectl -n ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG} delete all --all',
            'kubectl delete namespace ${CI_PROJECT_PATH_SLUG}-${CI_COMMIT_REF_SLUG}',
            '/delete_image.sh ${REGISTRY_DOMAIN_NAME}/${PROJECT_GROUP}/${PROJECT_NAME}:' . $scriptTag,
        ];
        foreach ($branchesModel->getBranches() as $branch) {
            $self->addOnly($branch);
        }
        foreach ($branchesModel->getBranchesToIgnore() as $branch) {
            $self->addExcept($branch);
        }
        $self->environment = [
            'name' => 'review/$CI_COMMIT_REF_NAME',
            'action' => 'stop',
        ];
        $self->manual = true;
        return $self;
    }
}
