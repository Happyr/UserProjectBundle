<?php

namespace HappyR\UserProjectBundle\Events;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class ProjectEvent
 *
 * @author Tobias Nyholm
 *
 */
class ProjectEvent extends Event
{
    /**
     * @var Project project
     *
     *
     */
    protected $project;

    /**
     * @var IdentifierInterface user
     *
     *
     */
    protected $user;

    /**
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     */
    public function __construct(Project &$project, ProjectMemberInterface &$user = null)
    {
        $this->project = $project;
        $this->user = $user;
    }

    /**
     *
     * @param \HappyR\UserProjectBundle\Entity\Project $project
     *
     * @return $this
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     *
     * @return \HappyR\UserProjectBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     *
     * @param \HappyR\ProjectMemberInterface $user
     *
     * @return $this
     */
    public function setUser(ProjectMemberInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     *
     * @return \HappyR\ProjectMemberInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
