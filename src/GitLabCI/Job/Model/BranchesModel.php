<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job\Model;

use TheAentMachine\AentGitLabCI\Aenthill\Metadata;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\Aenthill\Manifest;
use TheAentMachine\Exception\ManifestException;

final class BranchesModel
{
    /** @var string[] */
    private $branches;

    /** @var string[] */
    private $branchesToIgnore;

    /**
     * BranchesModel constructor.
     * @param string[] $branches
     * @param string[] $branchesToIgnore
     * @throws JobException
     */
    public function __construct(array $branches, array $branchesToIgnore = [])
    {
        if (empty($branches)) {
            throw JobException::branchIsNull();
        }
        $this->branches = $branches;
        $this->branchesToIgnore = $branchesToIgnore;
    }

    /**
     * @return BranchesModel
     * @throws JobException
     * @throws ManifestException
     */
    public static function newFromMetadata(): self
    {
        $branches = \explode(';', Manifest::mustGetMetadata(Metadata::BRANCHES_KEY));

        $branchesToIgnore = Manifest::getMetadata(Metadata::BRANCHES_TO_IGNORE_KEY);
        $branchesToIgnore = null === $branchesToIgnore ? [] : \explode(';', $branchesToIgnore);

        return new self($branches, $branchesToIgnore);
    }

    public function feedMetadata(): void
    {
        Manifest::addMetadata(Metadata::BRANCHES_KEY, \implode(';', $this->branches));
        if (!empty($this->branchesToIgnore)) {
            Manifest::addMetadata(Metadata::BRANCHES_TO_IGNORE_KEY, \implode(';', $this->branchesToIgnore));
        }
    }

    public function isSingleBranch(): bool
    {
        return (count($this->branches) === 1 && $this->branches[0] !== 'branches');
    }

    /**
     * @return string[]
     */
    public function getBranches(): array
    {
        return $this->branches;
    }

    /**
     * @return string[]
     */
    public function getBranchesToIgnore(): array
    {
        return $this->branchesToIgnore;
    }
}
