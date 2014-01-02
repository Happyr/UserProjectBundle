<?php

namespace HappyR\UserProjectBundle\Manager;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Events\JoinRequestEvent;
use HappyR\UserProjectBundle\Factory\ProjectFactory;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use HappyR\UserProjectBundle\Model\ProjectObjectInterface;
use HappyR\UserProjectBundle\ProjectEvents;
use HappyR\UserProjectBundle\Services\MailerService;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProjectManager
 *
 * @author Tobias Nyholm
 *
 * This is the class you should use when managing (adding/removing stuff) your projects
 *
 */
class ProjectManager
{
    /**
     * @var ObjectManager em
     *
     *
     */
    protected $em;

    /**
     * @var PermissionManager permissionManager
     *
     */
    protected $permissionManager;

    /**
     * @var ProjectFactory projectFactory
     *
     */
    protected $projectFactory;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface dispatcher
     *
     */
    protected $dispatcher;

    /**
     * @param ObjectManager $om
     * @param PermissionManager $pm
     * @param ProjectFactory $pf
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
     * Add a request to join the project
     *
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     *
     */
    public function addJoinRequest(Project &$project, ProjectMemberInterface &$user)
    {
        /**
         * Get admins
         */
        $administrators = array();
        foreach ($project->getUsers() as $u) {
            if ($project->getPermission($u) == 'MASTER') {
                $administrators[] = $u;
            }
        }

        //fire event
        $event = new JoinRequestEvent($project, $administrators, $user);
        $this->dispatcher->dispatch(ProjectEvents::USER_JOIN_REQUEST, $event);
    }

    /**
     * Remove private projects are revoke permission from other projects
     *
     * @param ProjectMemberInterface &$user
     *
     */
    public function removeUserFromAllProjects(ProjectMemberInterface &$user)
    {
        $repo = $this->em->getRepository('HappyRUserProjectBundle:Project');
        $privateProject = $repo->findPrivateProject($user);

        $this->projectFactory->remove($privateProject);

        $projects = $repo->findUserProjects($user);
        foreach ($projects as $p) {
            $this->permissionManager->removeUser($p, $user);
        }
    }

    /**
     * Add a user to a project
     *
     *
     * @param Project $project
     * @param ProjectMemberInterface &$user
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function addUser(Project $project, ProjectMemberInterface &$user)
    {
        //if you try to add a user that already is a part of the project
        if ($project->getUsers()->contains($user)) {
            return true;
        }

        /**
         * Make sure that the project is a public project, or create a new public project
         */
        if (!$project->isPublic()) {
            throw new \InvalidArgumentException('You can not add a user to a private project');
        }

        $this->permissionManager->addUser($project, $user);

        $this->em->persist($project);
        $this->em->flush();

        //fire event
        $event = new ProjectEvent($project, $user);
        $this->dispatcher->dispatch(ProjectEvents::USER_INVITED, $event);
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
        $this->permissionManager->removeUser($project, $user);

        $this->em->persist($project);
        $this->em->flush();

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

        return true;
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
        $this->permissionManager->removeObject($project, $object);

        $this->em->persist($project);
        $this->em->persist($object);
        $this->em->flush();
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
        $this->permissionManager->changePermissions($project, $user, $mask);

        $this->em->persist($project);
        $this->em->flush();
    }
}