<?php


namespace TheAentMachine\AentGitLabCI\Aenthill;

final class Metadata
{
    public const REGISTRY_DOMAIN_NAME_KEY = 'REGISTRY_DOMAIN_NAME';
    public const PROJECT_GROUP_KEY = 'PROJECT_GROUP';
    public const PROJECT_NAME_KEY = 'PROJECT_NAME';

    public const DEPLOY_TYPE_KEY = 'DEPLOY_TYPE';
    public const REMOTE_IP_KEY = 'REMOTE_IP';
    public const REMOTE_USER_KEY = 'REMOTE_USER';
    public const REMOTE_BASE_PATH_KEY = 'REMOTE_BASE_PATH';
    public const BRANCH_KEY = 'BRANCH';
    public const BRANCHES_TO_IGNORE_KEY = 'BRANCHES_TO_IGNORE';
    public const IS_MULTIPLE_BRANCHES_KEY = 'IS_MULTIPLE_BRANCHES';
    public const IS_MANUAL_KEY = 'IS_MANUAL';

    // values...
    public const DEPLOY_TYPE_REMOTE_SERVER = 'Remote server';
}
