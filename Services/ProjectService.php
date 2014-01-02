<?php


namespace HappyR\UserProjectBundle\Services;

use HappyR\UserProjectBundle\Entity\Project;
use Doctrine\Common\Persistence\ObjectManager;
use HappyR\UserProjectBundle\Factory\ProjectFactory;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use HappyR\UserProjectBundle\Model\ProjectObjectInterface;

/**
 * Class ProjectService
 *
 * @author Tobias Nyholm
 *
 */
class ProjectService
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager em
     *
     */
    protected $em;

    /**
     * @var \HappyR\UserProjectBundle\Factory\ProjectFactory projectFactory
     *
     */
    protected $projectFactory;


    /**
     * @param ObjectManager $em
     * @param ProjectFactory $pf
     */
    public function __construct(ObjectManager $em, ProjectFactory $pf)
    {
        $this->em = $em;
        $this->projectFactory = $pf;
    }

    /**
     * Get the administrator for an object
     *
     * @param ProjectObjectInterface &$object
     *
     * @return User|null
     */
    public function getAdministratorForObject(ProjectObjectInterface &$object)
    {
        if (null == $project = $object->getProject()) {
            return null;
        }

        return $this->getAdministrator($project);
    }

    /**
     * Get an administrator for a project
     *
     * @param Project &$project
     *
     * @return User|null
     */
    public function getAdministrator(Project &$project)
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

        return null;
    }

    /**
     * This will always return a private project. If there is none at the moment
     * we will create one.
     *
     * @param ProjectMemberInterface $user
     *
     * @return Project
     */
    public function getUserPrivateProject(ProjectMemberInterface &$user)
    {
        $project=$this->em->getRepository('HappyRUserProjectBundle:Project')
            ->findPrivateProject($user);

        if (!$project) {
            $project = $this->projectFactory->getNew();
            $this->projectFactory->makePrivate($project, $user);

            $this->em->persist($project);
            $this->em->flush();
        }

        return $project;
    }
}