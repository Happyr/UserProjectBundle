<?php


namespace HappyR\UserProjectBundle\Services;

use HappyR\UserProjectBundle\Entity\Project;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @param ObjectManager $em
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
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
}