<?php

namespace Happyr\UserProjectBundle\Event;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Model\ProjectObjectInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class ProjectObjectEvent extends Event
{
    /**
     * This event is dispatch when someone is added to a project.
     */
    const OBJECT_ADDED = 'happyr.user.project.object.added';

    /**
     * This event is dispatch when someone is removed to a project.
     */
    const OBJECT_REMOVED = 'happyr.user.project.object.removed';

    /**
     * @var ProjectObjectInterface
     */
    protected $object;

    /**
     * @var Project
     */
    protected $project;

    /**
     * @param Project                $project
     * @param ProjectObjectInterface $object
     */
    public function __construct(Project $project, ProjectObjectInterface $object)
    {
        $this->object = $object;
        $this->project = $project;
    }

    /**
     * @return \Happyr\UserProjectBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return ProjectObjectInterface
     */
    public function getObject()
    {
        return $this->object;
    }
}
