<?php


namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Exception\AenthillException;

class GitLabCIFileException extends AenthillException
{
    public static function missingFile(): self
    {
        return new self('GitLab CI file does not exist');
    }
}