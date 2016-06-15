<?php

namespace Happyr\UserProjectBundle\Manager;

use Happyr\UserProjectBundle\Entity\Project;
use Happyr\UserProjectBundle\Event\ProjectEvent;
use Happyr\UserProjectBundle\Event\ProjectObjectEvent;
use Happyr\UserProjectBundle\Factory\ProjectFactory;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;
use Happyr\UserProjectBundle\Model\ProjectObjectInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProjectManager.
 *
 * @author Tobias Nyholm
 *
 * This is the class you should use when managing (adding/removing stuff) your projects
 */
class ProjectManager
{
    /**
     * @var ObjectManager em
     */
    protected $em;

    /**
     * @var PermissionManager permissionManager
     */
    protected $permissionManager;

    /**
     * @var ProjectFactory projectFactory
     */
    protected $projectFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface dispatcher
     */
    protected $dispatcher;

    /**
     * @param ObjectManager            $om
     * @param PermissionManager        $pm
     * @param ProjectFactory           $pf
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(
        ObjectManager $om,
        PermissionManager $pm,
        ProjectFactory $pf,
        EventDispatcherInterface $dispatcher
    ) {
        $this->em = $om;
        $this->permissionManager = $pm;
        $this->projectFactory = $pf;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Remove private projects are revoke permission from other projects.
     *
     * @param ProjectMemberInterface $user
     */
    public function removeUserFromAllProjects(ProjectMemberInterface $user)
    {
        $repo = $this->em->getRepository('HappyrUserProjectBundle:Project');
        $privateProject = $repo->findPrivateProject($user);

        $this->projectFactory->remove($privateProject);

        $projects = $repo->findUserProjects($user);
        foreach ($projects as $p) {
            $this->permissionManager->removeUser($p, $user);
        }
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
     *
     * @throws \InvalidArgumentException
     */
    public function addUser(Project $project, ProjectMemberInterface $user, $mask = 'VIEW')
    {
        //if you try to add a user that already is a part of the project
        if ($project->getUsers()->contains($user)) {
            return true;
        }

        /**
         * Make sure that the project is a public project, or create a new public project.
         */
        if (!$project->isPublic()) {
            throw new \InvalidArgumentException('You can not add a user to a private project');
        }

        $this->permissionManager->addUser($project, $user, $mask);

        $this->em->persist($project);
        $this->em->flush();

        // Dispatch event
        $this->dispatcher->dispatch(ProjectEvent::USER_ADDED, new ProjectEvent($project, $user));
    }

    /**
     * Remove user.
     *
     * @param Project                $project
     * @param ProjectMemberInterface $user
     */
    public function removeUser(Project $project, ProjectMemberInterface $user)
    {
        $this->permissionManager->removeUser($project, $user);

        $this->em->persist($project);
        $this->em->flush();

        // Dispatch event
        $this->dispatcher->dispatch(ProjectEvent::USER_REMOVED, new ProjectEvent($project, $user));
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
        /*
         * Check if the object belongs to an other project
         */
        if ($object->getProject() != null) {
            $objectProject = $object->getProject();

            $this->removeObject($objectProject, $object);
            $this->em->persist($objectProject);
        }

        //add the object to this project
        $this->permissionManager->addObject($project, $object);

        $this->em->persist($project);
        $this->em->persist($object);
        $this->em->flush();

        // Dispatch event
        $this->dispatcher->dispatch(ProjectObjectEvent::OBJECT_ADDED, new ProjectEvent($project, $object));

        return true;
    }

    /**
     * Remove object from project.
     *
     * @param Project                $project
     * @param ProjectObjectInterface $object
     */
    public function removeObject(Project $project, ProjectObjectInterface $object)
    {
        $this->permissionManager->removeObject($project, $object);

        $this->em->persist($project);
        $this->em->persist($object);
        $this->em->flush();

        $this->dispatcher->dispatch(ProjectObjectEvent::OBJECT_REMOVED, new ProjectEvent($project, $object));
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
        $this->permissionManager->changePermissions($project, $user, $mask);

        $this->em->persist($project);
        $this->em->flush();
    }
}
