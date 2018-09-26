<?php

namespace TheAentMachine\AentGitLabCI\Context;

use TheAentMachine\Aent\Context\Context;
use TheAentMachine\AentGitLabCI\GitLabCI\Job\Model\BranchesModel;
use TheAentMachine\Aenthill\Aenthill;

final class BaseGitLabCIContext extends Context
{
    /** @var string */
    private $registryDomainName;

    /** @var string */
    private $projectGroup;

    /** @var string */
    private $projectName;

    /** @var BranchesModel */
    private $branchesModel;

    /**
     * BaseGitLabCIContext constructor.
     */
    public function __construct()
    {
        $context = Context::fromMetadata();
        parent::__construct($context->getEnvironmentType(), $context->getEnvironmentName());
        $this->registryDomainName = Aenthill::metadata('REGISTRY_DOMAIN_NAME');
        $this->projectGroup = Aenthill::metadata('PROJECT_GROUP');
        $this->projectName = Aenthill::metadata('PROJECT_NAME');
    }

    /**
     * @return void
     */
    public function toMetadata(): void
    {
        parent::toMetadata();
        Aenthill::update([
            'REGISTRY_DOMAIN_NAME' => $this->registryDomainName,
            'PROJECT_GROUP' => $this->projectGroup,
            'PROJECT_NAME' => $this->projectName,
        ]);
        $this->branchesModel->toMetadata();
    }

    /**
     * @return self
     */
    public static function fromMetadata(): self
    {
        $self = new self();
        $self->registryDomainName = Aenthill::metadata('REGISTRY_DOMAIN_NAME');
        $self->projectGroup = Aenthill::metadata('PROJECT_GROUP');
        $self->projectName = Aenthill::metadata('PROJECT_NAME');
        $self->branchesModel = BranchesModel::fromMetadata();
        return $self;
    }

    /**
     * @return string
     */
    public function getRegistryDomainName(): string
    {
        return $this->registryDomainName;
    }

    /**
     * @param string $registryDomainName
     * @return void
     */
    public function setRegistryDomainName(string $registryDomainName): void
    {
        $this->registryDomainName = $registryDomainName;
    }

    /**
     * @return string
     */
    public function getProjectGroup(): string
    {
        return $this->projectGroup;
    }

    /**
     * @param string $projectGroup
     * @return void
     */
    public function setProjectGroup(string $projectGroup): void
    {
        $this->projectGroup = $projectGroup;
    }

    /**
     * @return string
     */
    public function getProjectName(): string
    {
        return $this->projectName;
    }

    /**
     * @param string $projectName
     * @return void
     */
    public function setProjectName(string $projectName): void
    {
        $this->projectName = $projectName;
    }

    /**
     * @return BranchesModel
     */
    public function getBranchesModel(): BranchesModel
    {
        return $this->branchesModel;
    }

    /**
     * @param BranchesModel $branchesModel
     * @return void
     */
    public function setBranchesModel(BranchesModel $branchesModel): void
    {
        $this->branchesModel = $branchesModel;
    }
}
