<?php

namespace HappyR\UserProjectBundle\Events;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class ProjectEvent
 *
 * @author Tobias Nyholm
 *
 */
class ProjectEvent extends BaseEvent
{
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
    public function __construct(Project &$project, ProjectMemberInterface &$user)
    {
        $this->user = $user;

        parent::__construct($project);
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
