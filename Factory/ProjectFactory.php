<?php

namespace HappyR\UserProjectBundle\Factory;

use Doctrine\Common\Persistence\ObjectManager;
use HappyR\IdentifierInterface;
use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Manager\PermissionManager;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class ProjectFactory
 *
 * @author Tobias Nyholm
 *
 *
 * A Project factory creates, saves and removes projects
 */
class ProjectFactory
{
    /**
     * @var ObjectManager em
     *
     */
    private $em;

    /**
     * @var PermissionManager permissionManager
     *
     */
    private $permissionManager;

    /**
     * @param ObjectManager $em
     * @param PermissionManager $pm
     */
    public function __construct(ObjectManager $em, PermissionManager $pm)
    {
        $this->em = $em;
        $this->permissionManager = $pm;

    }

    /**
     * Returns a new object with all empty values
     *
     * @return Project
     */
    public function getNew()
    {
        return new Project();
    }

    /**
     * Mark a project as private
     *
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     *
     */
    public function makePrivate(Project &$project, ProjectMemberInterface &$user)
    {
        $project
            ->setName('_private_' . $user->getId())
            ->setPublic(false);
    }

    /**
     * Saves the projects
     *
     * @param Project $project
     *
     */
    public function create(Project &$project)
    {
        $this->em->persist($project);
        $this->em->flush();
    }

    /**
     * Remove a project
     *
     * @param Project &$project
     *
     */
    public function remove(Project &$project)
    {
        $users = $project->getUsers();

        foreach ($users as $user) {
            $this->permissionManager->removeUser($project, $user);
        }

        $opuses = $project->getObjects();
        foreach ($opuses as $o) {
            $o->removeProject();
            $this->em->persist($o);
        }
        $this->em->flush();

        $this->em->remove($project);
        $this->em->flush();
    }
}
