<?php

namespace HappyR\UserProjectBundle;

/**
 * Class ProjectEvents
 *
 * @author Tobias Nyholm
 *
 */
final class ProjectEvents
{
    /**
     * This event is thrown when someone is invited to a project
     *
     * The event listener receives an
     * HappyR\UserProjectBundle\Events\Project instance.
     *
     * @var string
     */
    const USER_INVITED = 'happyr.user.project.user.invited';
}
