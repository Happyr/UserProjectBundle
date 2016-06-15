<?php

namespace Happyr\UserProjectBundle\Event;

use Happyr\UserProjectBundle\Entity\Project;

/**
 * @author Tobias Nyholm
 */
class ProjectEvent extends BaseEvent
{
    /**
     * This event is dispatch when someone is added to a project.
     */
    const USER_ADDED = 'happyr.user.project.user.added';

    /**
     * This event is dispatch when someone is removed to a project.
     */
    const USER_REMOVED = 'happyr.user.project.user.removed';
}
