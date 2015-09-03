<?php

namespace Happyr\UserProjectBundle\Factory;

use Doctrine\Common\Persistence\ObjectManager;
use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Manager\PermissionManager;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class ProjectFactory.
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
     */
    private $em;

    /**
     * @var PermissionManager permissionManager
     */
    private $permissionManager;

    /**
     * @param ObjectManager     $em
     * @param PermissionManager $pm
     */
    public function __construct(ObjectManager $em, PermissionManager $pm)
    {
        $this->em = $em;
        $this->permissionManager = $pm;
    }

    /**
     * Returns a new object with all empty values.
     *
     * @return Project
     */
    public function getNew()
    {
        return new Project();
    }

    /**
     * Mark a project as private.
     *
     * @param Project                &$project
     * @param ProjectMemberInterface &$user
     */
    public function makePrivate(Project &$project, ProjectMemberInterface &$user)
    {
        $project
            ->setName('_private_'.$user->getId())
            ->setPublic(false)
            ->addUser($user);
    }

    /**
     * Clone a private project. Get a new project with owner of the private one.
     *
     * You have to name the new project and add objects to it.
     *
     * @param Project $project
     *
     * @return Project
     */
    public function clonePrivateProject(Project $project)
    {
        $owner = $project->getUsers()->first();

        $project = $this->getNew();
        $project->setName('Project - '.$owner->getId());

        $this->create($project);
        $this->permissionManager->addUser($project, $owner, 'OWNER');

        return $project;
    }

    /**
     * Saves the projects.
     *
     * @param Project $project
     */
    public function create(Project &$project)
    {
        $this->em->persist($project);
        $this->em->flush();

        if (!$project->isPublic()) {
            //make the user master over his private project
            $user = $project->getUsers()->first();
            $this->permissionManager->addUser($project, $user, 'OWNER');
        }
    }

    /**
     * Remove a project.
     *
     * @param Project &$project
     */
    public function remove(Project &$project)
    {
        $users = $project->getUsers();

        foreach ($users as $user) {
            $this->permissionManager->removeUser($project, $user);
        }

        $objects = $project->getObjects();
        foreach ($objects as $o) {
            $o->removeProject();
            $this->em->persist($o);
        }
        $this->em->flush();

        $this->em->remove($project);
        $this->em->flush();
    }
}
