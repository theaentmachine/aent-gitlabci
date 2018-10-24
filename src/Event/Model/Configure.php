<?php

namespace TheAentMachine\AentGitLabCI\Event\Model;

use TheAentMachine\Aent\Payload\JsonPayloadInterface;

final class Configure implements JsonPayloadInterface
{
    /** @var string */
    private $registryDomainName;

    /** @var string */
    private $projectGroup;

    /** @var string */
    private $projectName;

    /**
     * GitLabCIContext constructor.
     * @param string $registryDomainName
     * @param string $projectGroup
     * @param string $projectName
     */
    public function __construct(string $registryDomainName, string $projectGroup, string $projectName)
    {
        $this->registryDomainName = $registryDomainName;
        $this->projectGroup = $projectGroup;
        $this->projectName = $projectName;
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'REGISTRY_DOMAIN_NAME' => $this->registryDomainName,
            'PROJECT_GROUP' => $this->projectGroup,
            'PROJECT_NAME' => $this->projectName,
        ];
    }

    /**
     * @param array<string,string> $assoc
     * @return self
     */
    public static function fromArray(array $assoc): self
    {
        $registryDomainName = $assoc['REGISTRY_DOMAIN_NAME'];
        $projectGroup = $assoc['PROJECT_GROUP'];
        $projectName = $assoc['PROJECT_NAME'];
        return new self($registryDomainName, $projectGroup, $projectName);
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
     */
    public function setProjectName(string $projectName): void
    {
        $this->projectName = $projectName;
    }
}
