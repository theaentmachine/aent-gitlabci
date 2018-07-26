<?php


namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Exception\AenthillException;

final class PayloadException extends AenthillException
{
    public static function missingKey(string $key): self
    {
        return new self("\"$key\" is a required entry in the payload");
    }
}