<?php

namespace Happyr\UserProjectBundle\Service;

use Happyr\UserProjectBundle\Entity\Project;
use Doctrine\Common\Persistence\ObjectManager;
use Happyr\UserProjectBundle\Event\JoinRequestEvent;
use Happyr\UserProjectBundle\Factory\ProjectFactory;
use Happyr\UserProjectBundle\Model\ProjectMemberInterface;
use Happyr\UserProjectBundle\Model\ProjectObjectInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProjectService.
 *
 * @author Tobias Nyholm
 */
class ProjectService
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager em
     */
    protected $em;

    /**
     * @var \Happyr\UserProjectBundle\Factory\ProjectFactory projectFactory
     */
    protected $projectFactory;

    /**
     * @var EventDispatcherInterface dispatcher
     */
    protected $dispatcher;

    /**
     * @param ObjectManager            $em
     * @param ProjectFactory           $pf
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ObjectManager $em, ProjectFactory $pf, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->projectFactory = $pf;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Get the administrator for an object.
     *
     * @param ProjectObjectInterface $object
     *
     * @return ProjectMemberInterface|null
     */
    public function getAdministratorForObject(ProjectObjectInterface $object)
    {
        if (null == $project = $object->getProject()) {
            return;
        }

        return $this->getAdministrator($project);
    }

    /**
     * Get an administrator for a project.
     *
     * @param Project $project
     *
     * @return ProjectMemberInterface|null
     */
    public function getAdministrator(Project $project)
    {
        $user = $project->getUsers()->filter(
            function ($user) use ($project) {
                if ($project->getPermission($user) == 'MASTER') {
                    return $user;
                }
            }
        )->first();

        if ($user !== false) {
            return $user;
        }

        return;
    }

    /**
     * This will always return a private project. If there is none at the moment
     * we will create one.
     *
     * @param ProjectMemberInterface $user
     *
     * @return Project
     */
    public function getUserPrivateProject(ProjectMemberInterface $user)
    {
        $project = $this->em->getRepository('HappyrUserProjectBundle:Project')
            ->findPrivateProject($user);

        if (!$project) {
            $project = $this->projectFactory->getNew();
            $this->projectFactory->makePrivate($project, $user);
            $this->projectFactory->create($project);
        }

        return $project;
    }

    /**
     * Add a request to join the project.
     *
     * @param Project                $project
     * @param ProjectMemberInterface $user
     */
    public function addJoinRequest(Project $project, ProjectMemberInterface $user)
    {
        /**
         * Get admins.
         */
        $administrators = array();
        foreach ($project->getUsers() as $u) {
            if ($project->getPermission($u) == 'MASTER') {
                $administrators[] = $u;
            }
        }

        //fire event
        $event = new JoinRequestEvent($project, $administrators, $user);
        $this->dispatcher->dispatch(JoinRequestEvent::USER_JOIN_REQUEST, $event);
    }
}
