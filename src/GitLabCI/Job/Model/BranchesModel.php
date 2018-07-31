<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job\Model;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Exception\ManifestException;

final class BranchesModel
{
    /** @var bool */
    private $isMultipleBranches;

    /** @var string|null */
    private $branch;

    /** @var string[] */
    private $branchesToIgnore = [];

    /**
     * BranchesModel constructor.
     * @param bool $isMultipleBranches
     * @param null|string $branch
     * @param string[] $branchesToIgnore
     */
    public function __construct(bool $isMultipleBranches, ?string $branch, array $branchesToIgnore = [])
    {
        $this->isMultipleBranches = $isMultipleBranches;
        $this->branch = $branch;
        $this->branchesToIgnore = $branchesToIgnore;
    }

    /**
     * @return BranchesModel
     * @throws ManifestException
     */
    public static function newFromMetadata(): self
    {
        $isMultipleBranches = Manifest::mustGetMetadata(Metadata::IS_MULTIPLE_BRANCHES_KEY);
        $isMultipleBranches = boolval($isMultipleBranches);

        if ($isMultipleBranches) {
            $branchesToIgnore = Manifest::getMetadata(Metadata::BRANCHES_TO_IGNORE_KEY);
            $branchesToIgnore = empty($branchesToIgnore) ? [] : \explode(';', $branchesToIgnore);

            return new self($isMultipleBranches, null, $branchesToIgnore);
        }

        $branch = Manifest::mustGetMetadata(Metadata::BRANCH_KEY);

        return new self($isMultipleBranches, $branch);
    }

    /**
     * @throws JobException
     */
    public function feedMetadata(): void
    {
        Manifest::addMetadata(Metadata::IS_MULTIPLE_BRANCHES_KEY, "" . $this->isMultipleBranches);

        if ($this->isMultipleBranches && !empty($this->branchesToIgnore)) {
            Manifest::addMetadata(Metadata::BRANCHES_TO_IGNORE_KEY, \implode(';', $this->branchesToIgnore));
        } elseif (!$this->isMultipleBranches) {
            if (empty($this->branch)) {
                throw JobException::branchIsNull();
            }

            Manifest::addMetadata(Metadata::BRANCH_KEY, $this->branch);
        }
    }

    /**
     * @return bool
     */
    public function isMultipleBranches(): bool
    {
        return $this->isMultipleBranches;
    }

    /**
     * @return null|string
     */
    public function getBranch(): ?string
    {
        return $this->branch;
    }

    /**
     * @return string[]
     */
    public function getBranchesToIgnore(): array
    {
        return $this->branchesToIgnore;
    }
}
