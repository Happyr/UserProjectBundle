<?php

namespace HappyR\UserProjectBundle\Factory;

use Carlin\BaseBundle\Factory\BaseFactory;
use HappyR\IdentifierInterface;
use HappyR\UserProjectBundle\Entity\Project;

/**
 * Class ProjectFactory
 *
 * @author Tobias Nyholm
 *
 */
class ProjectFactory extends BaseFactory
{
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
     * @param IdentifierInterface &$user
     *
     */
    public function makePrivate(Project &$project, IdentifierInterface &$user)
    {
        $project
            ->setName('_private_' . $user->getId())
            ->setPublic(false);
    }

    /**
     * Remove a project
     *
     * @param mixed &$project
     *
     */
    public function remove(&$project)
    {
        $users = $project->getUsers();
        $pm = $this->getParam('permission_manager');

        foreach ($users as $user) {
            $pm->removeUser($project, $user);
        }

        $em = $this->getParam('em');
        $opuses = $project->getOpuses();
        foreach ($opuses as $o) {
            $o->removeProject();
            $em->persist($o);
        }
        $em->flush();

        $em->remove($project);
        $em->flush();
    }
}
