<?php

namespace Happyr\UserProjectBundle\Event;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * @author Tobias Nyholm
 */
class JoinRequestEvent extends BaseEvent
{
    /**
     * This event is fired when someone wants to join a project.
     *
     * @var string
     */
    const USER_JOIN_REQUEST = 'happyr.user.project.user.join_request';

    /**
     * @var array administrators
     *
     * This is an array of ProjectMemberInterface
     */
    protected $administrators;

    /**
     * @param Project                $project
     * @param array                  $admins
     * @param ProjectMemberInterface $user
     */
    public function __construct(Project $project, array $admins, ProjectMemberInterface $user)
    {
        $this->administrators = $admins;

        parent::__construct($project, $user);
    }

    /**
     * @param array $user
     *
     * @return $this
     */
    public function setAdministrators(array $admins)
    {
        $this->administrators = $admins;

        return $this;
    }

    /**
     * @return array
     */
    public function getAdministrators()
    {
        return $this->administrators;
    }
}
