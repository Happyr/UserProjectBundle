<?php

namespace HappyR\UserProjectBundle\Events;

use HappyR\IdentifierInterface;
use HappyR\UserProjectBundle\Entity\Project;
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
     * @param IdentifierInterface &$user
     */
    public function __construct(Project &$project, IdentifierInterface &$user = null)
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
     * @param \HappyR\IdentifierInterface $user
     *
     * @return $this
     */
    public function setUser(IdentifierInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     *
     * @return \HappyR\IdentifierInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
