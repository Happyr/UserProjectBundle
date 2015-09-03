<?php

namespace Happyr\UserProjectBundle\Event;

use Happyr\UserProjectBundle\Entity\Project;

/**
 * @author Tobias Nyholm
 */
class ProjectEvent extends BaseEvent
{
    /**
     * This event is fired when someone is invited to a project.
     *
     * @var string
     */
    const USER_ADDED = 'happyr.user.project.user.added';
}
