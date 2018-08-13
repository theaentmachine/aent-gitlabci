<?php


namespace TheAentMachine\AentGitLabCI\Exception;

use TheAentMachine\Aenthill\CommonEvents;
use TheAentMachine\Exception\AenthillException;

final class PayloadException extends AenthillException
{
    public static function emptyPayload(string $event): self
    {
        return new self("$event event requires a payload");
    }

    public static function missingDockerComposeFilename(): self
    {
        return new self(CommonEvents::NEW_DEPLOY_DOCKER_COMPOSE_JOB_EVENT . ' event requires a payload with the Docker Compose filename');
    }

    public static function missingKubernetesPathname(): self
    {
        return new self(CommonEvents::NEW_DEPLOY_KUBERNETES_JOB_EVENT . ' event requires a payload with the Kubernetes pathname');
    }

    public static function missingServiceName(): self
    {
        return new self(CommonEvents::NEW_BUILD_IMAGE_JOB_EVENT . ' event requires a payload with the service name');
    }

    public static function missingDockerfileName(): self
    {
        return new self(CommonEvents::NEW_BUILD_IMAGE_JOB_EVENT . ' event requires a payload with the Dockerfile name');
    }
}
