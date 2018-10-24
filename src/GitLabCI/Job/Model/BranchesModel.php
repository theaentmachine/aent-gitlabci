<?php


namespace TheAentMachine\AentGitLabCI\GitLabCI\Job\Model;

use TheAentMachine\Aent\Context\ContextInterface;
use TheAentMachine\AentGitLabCI\Exception\JobException;
use TheAentMachine\Aenthill\Aenthill;

final class BranchesModel implements ContextInterface
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
     * @return void
     */
    public function toMetadata(): void
    {
        Aenthill::update([
            'BRANCHES' => \implode(';', $this->branches),
            'BRANCHES_TO_IGNORE' => \implode(';', $this->branchesToIgnore),
        ]);
    }

    /**
     * @return self
     * @throws JobException
     */
    public static function fromMetadata(): self
    {
        $branches = \explode(';', Aenthill::metadata('BRANCHES'));
        $branchesToIgnore = \explode(';', Aenthill::metadata('BRANCHES_TO_IGNORE'));
        return new self($branches, $branchesToIgnore);
    }

    /**
     * @return bool
     */
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
