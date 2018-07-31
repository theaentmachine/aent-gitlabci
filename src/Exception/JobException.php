<?php


namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Exception\AenthillException;

final class JobException extends AenthillException
{
    public static function cannotAddOnly(string $identifier): self
    {
        return new self("\"$identifier\" is already referenced in \"except\" section");
    }

    public static function cannotAddExcept(string $identifier): self
    {
        return new self("\"$identifier\" is already referenced in \"only\" section");
    }

    public static function branchIsNull(): self
    {
        return new self('"only" section cannot have a null entry');
    }

    public static function unknownDeployType(string $deployType): self
    {
        return new self("\"$deployType\" is not a valid deploy type.");
    }
}
