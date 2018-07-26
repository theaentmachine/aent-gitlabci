<?php


namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Exception\AenthillException;

final class PayloadException extends AenthillException
{
    public static function missingDockerComposeFilename(): self
    {
        return new self(CommonEvents::NEW_DEPLOY_JOB_DOCKER_COMPOSE_EVENT . ' event requires a payload with the Docker Compose filename');
    }
}