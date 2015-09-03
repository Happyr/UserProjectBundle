<?php

namespace Happyr\UserProjectBundle\Events;

use Happyr\UserProjectBundle\Entity\Project;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BaseEvent.
 *
 * @author Tobias Nyholm
 */
class BaseEvent extends Event
{
    /**
     * @var Project project
     */
    protected $project;

    /**
     * @param Project &$project
     */
    public function __construct(Project &$project)
    {
        $this->project = $project;
    }

    /**
     * @param \Happyr\UserProjectBundle\Entity\Project $project
     *
     * @return $this
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * @return \Happyr\UserProjectBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
