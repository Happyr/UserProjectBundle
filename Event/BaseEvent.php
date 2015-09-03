<?php

namespace Happyr\UserProjectBundle\Event;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class BaseEvent.
 *
 * @author Tobias Nyholm
 */
class BaseEvent extends Event
{
    /**
     * @var ProjectMemberInterface user
     */
    protected $user;

    /**
     * @var Project project
     */
    protected $project;

    /**
     * @param Project                $project
     * @param ProjectMemberInterface $user
     */
    public function __construct(Project $project, ProjectMemberInterface $user)
    {
        $this->user = $user;
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

    /**
     * @param ProjectMemberInterface $user
     *
     * @return $this
     */
    public function setUser(ProjectMemberInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return ProjectMemberInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
