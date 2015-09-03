<?php

namespace Happyr\UserProjectBundle\Events;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class JoinRequestEvent
 *
 * @author Tobias Nyholm
 *
 */
class JoinRequestEvent extends BaseEvent
{
    /**
     * @var array administrators
     *
     * This is an array of ProjectMemberInterface
     */
    protected $administrators;

    /**
     * @var ProjectMemberInterface user
     *
     * This is the user that wants to join
     *
     */
    protected $user;

    /**
     * @param Project &$project
     * @param array &$admins
     * @param ProjectMemberInterface &$user
     */
    public function __construct(Project &$project, array &$admins, ProjectMemberInterface &$user)
    {
        $this->administrators = $admins;
        $this->user = $user;

        parent::__construct($project);
    }

    /**
     *
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
     *
     * @return array
     */
    public function getAdministrators()
    {
        return $this->administrators;
    }

    /**
     *
     * @param \Happyr\UserProjectBundle\Model\ProjectMemberInterface $user
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
     * @return \Happyr\UserProjectBundle\Model\ProjectMemberInterface
     */
    public function getUser()
    {
        return $this->user;
    }


}
