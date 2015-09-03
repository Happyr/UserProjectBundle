<?php

namespace Happyr\UserProjectBundle;

/**
 * Class ProjectEvents
 *
 * @author Tobias Nyholm
 *
 */
final class ProjectEvents
{
    /**
     * This event is fired when someone is invited to a project
     *
     * The event listener receives an
     * Happyr\UserProjectBundle\Events\Project instance.
     *
     * @var string
     */
    const USER_INVITED = 'happyr.user.project.user.invited';

    /**
     * This event is fired when someone wants to join a project
     *
     * The event listener receives an
     * Happyr\UserProjectBundle\Events\JoinRequestEvent instance.
     *
     * @var string
     */
    const USER_JOIN_REQUEST = 'happyr.user.project.user.join_request';
}
