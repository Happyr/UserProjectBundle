<?php

namespace Happyr\UserProjectBundle\Manager;

use Happyr\UserProjectBundle\Model\ProjectObjectInterface;
use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class SecureProjectManager.
 *
 * @author Tobias Nyholm
 *
 * This project manager check if the current user has permissions to execute the tasks in the project manager
 */
class SecureProjectManager extends ProjectManager
{
    /**
     * @var SecurityManager securityManager
     */
    protected $securityManager;

    /**
     * @param \Happyr\UserProjectBundle\Manager\SecurityManager $securityManager
     */
    public function setSecurityManager(SecurityManager $securityManager)
    {
        $this->securityManager = $securityManager;
    }

    /**
     * Add a user to a project.
     *
     *
     * @param Project                $project
     * @param ProjectMemberInterface $user
     * @param string                 $mask
     *
     * @return bool
     */
    public function addUser(Project $project, ProjectMemberInterface $user, $mask = 'VIEW')
    {
        $this->securityManager->verifyProjectIsPublic($project);
        $this->securityManager->verifyUserIsGranted('MASTER', $project);

        return parent::addUser($project, $user, $mask);
    }

    /**
     * Add object to project.
     *
     * WARNING: This will remove the object from any previuos projects
     *
     * @param Project                $project
     * @param ProjectObjectInterface $object
     *
     * @return bool
     */
    public function addObject(Project $project, ProjectObjectInterface $object)
    {
        $this->securityManager->verifyUserIsGranted('CREATE', $project);

        return parent::addObject($project, $object);
    }

    /**
     * Remove user.
     *
     * @param Project                $project
     * @param ProjectMemberInterface $user
     */
    public function removeUser(Project $project, ProjectMemberInterface $user)
    {
        $this->securityManager->verifyUserIsGranted('MASTER', $project);

        parent::removeUser($project, $user);
    }

    /**
     * Remove object from project.
     *
     * @param Project                $project
     * @param ProjectObjectInterface $object
     */
    public function removeObject(Project $project, ProjectObjectInterface $object)
    {
        $this->securityManager->verifyUserIsGranted('DELETE', $project);

        parent::removeObject($project, $user);
    }

    /**
     * Alias for PermissionManager.
     *
     * @param Project                $project
     * @param ProjectMemberInterface $user
     * @param string                 $mask
     */
    public function changeUserPermissions(Project $project, ProjectMemberInterface $user, $mask)
    {
        $this->securityManager->verifyUserIsGranted('MASTER', $project);

        parent::changeUserPermissions($project, $user, $mask);
    }
}
