<?php


namespace HappyR\UserProjectBundle\Manager;

use HappyR\UserProjectBundle\Events\JoinRequestEvent;
use HappyR\UserProjectBundle\Model\ProjectObjectInterface;
use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use HappyR\UserProjectBundle\ProjectEvents;

/**
 * Class SecureProjectManager
 *
 * @author Tobias Nyholm
 *
 * This project manager check if the current user has permissions to execute the tasks in the project manager
 *
 */
class SecureProjectManager extends ProjectManager
{
    /**
     * @var SecurityManager securityManager
     *
     */
    protected $securityManager;

    /**
     *
     * @param \HappyR\UserProjectBundle\Manager\SecurityManager $securityManager
     *
     */
    public function setSecurityManager(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    public function addJoinRequest(Project &$project, ProjectMemberInterface &$user)
    {
        $this->securityManager->verifyProjectIsPublic($project);

        parent::addJoinRequest($project, $user);
    }

    /**
     * Add a user to a project
     *
     *
     * @param Project $project
     * @param ProjectMemberInterface &$user
     *
     * @return bool
     */
    public function addUser(Project $project, ProjectMemberInterface &$user)
    {
        $this->securityManager->verifyProjectIsPublic($project);
        $this->securityManager->verifyUserIsGranted('MASTER', $project);

        return parent::addUser($project, $user);
    }

    /**
     * Add object to project.
     *
     * WARNING: This will remove the object from any previuos projects
     *
     * @param Project $project
     * @param ProjectObjectInterface $object
     *
     * @return boolean
     */
    public function addObject(Project $project, ProjectObjectInterface &$object)
    {
        $this->securityManager->verifyUserIsGranted('CREATE', $project);

        return parent::addObject($project, $user);
    }

    /**
     * Remove user
     *
     * @param Project $project
     * @param ProjectMemberInterface &$user
     *
     */
    public function removeUser(Project $project, ProjectMemberInterface &$user)
    {
        $this->securityManager->verifyUserIsGranted('MASTER', $project);

        return parent::removeUser($project, $user);
    }

    /**
     * Remove object from project
     *
     * @param Project $project
     * @param ProjectObjectInterface $object
     *
     */
    public function removeObject(Project $project, ProjectObjectInterface &$object)
    {
        $this->securityManager->verifyUserIsGranted('DELETE', $project);

        return parent::removeObject($project, $user);
    }

    /**
     * Alias for PermissionManager
     *
     * @param Project $project
     * @param ProjectMemberInterface $user
     * @param string $mask
     *
     */
    public function changeUserPermissions(Project $project, ProjectMemberInterface $user, $mask)
    {
        $this->securityManager->verifyUserIsGranted('MASTER', $project);

        return parent::changeUserPermissions($project, $user, $mask);
    }
}