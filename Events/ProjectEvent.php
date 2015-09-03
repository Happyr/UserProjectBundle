<?php

namespace Happyr\UserProjectBundle\Events;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class ProjectEvent
 *
 * @author Tobias Nyholm
 *
 */
class ProjectEvent extends BaseEvent
{
    /**
     * @var ProjectMemberInterface user
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
     * @param \Happyr\ProjectMemberInterface $user
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
     * @return \Happyr\ProjectMemberInterface
     */
    public function getUser()
    {
        return $this->user;
    }
}
