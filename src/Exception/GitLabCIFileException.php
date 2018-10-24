<?php

namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Exception\AenthillException;

final class GitLabCIFileException extends AenthillException
{
    /**
     * @return self
     */
    public static function missingFile(): self
    {
        return new self('GitLab CI file does not exist');
    }
}
