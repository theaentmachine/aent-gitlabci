<?php


namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Exception\AenthillException;

class JobInstructionsException extends AenthillException
{
    public static function cannotAddOnly(string $branch): self
    {
        return new self("\"$branch\" is already referenced in \"except\" section");
    }

    public static function cannotAddExcept(string $branch): self
    {
        return new self("\"$branch\" is already referenced in \"only\" section");
    }
}