<?php

namespace HappyR\UserProjectBundle\Entity;

use Doctrine\ORM\EntityRepository;
use HappyR\IdentifierInterface;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;

/**
 * Class ProjectRepository
 *
 * @author Tobias Nyholm
 *
 */
class ProjectRepository extends EntityRepository
{

    /**
     * Get the private project for a user.
     *
     * @param ProjectMemberInterface &$user
     *
     * @return Project|null
     */
    public function findPrivateProject(ProjectMemberInterface &$user)
    {
        return $this->findOneBy(
            array(
                'name' => '_private_' . $user->getId(),
                'public' => false,
            )
        );
    }

    /**
     * Find the projects that this user is a member of.
     * This will not fetch private projects.
     *
     * @param ProjectMemberInterface &$user
     *
     * @return array
     */
    public function findUserProjects(ProjectMemberInterface &$user)
    {

        $query = $this->getUserProjectsQb()
            ->setParameter('user_id', $user->getId())
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Get query builder for user project
     *
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getUserProjectsQb()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb
            ->select('e')
            ->from('HappyRUserProjectBundle:Project', 'e')
            ->join('e.users', 'u')
            ->where('u.id = :user_id')
            ->andWhere('e.public = 1');
    }

    /**
     * Find the projects that this user is not a member of.
     * This will not fetch private projects.
     *
     * @param ProjectMemberInterface &$user
     *
     * @return array
     */
    public function findNonUserProjects(ProjectMemberInterface &$user)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        $query = $qb
            ->select('p')
            ->from('HappyRUserProjectBundle:Project', 'p')
            ->andWhere('p.public = 1')
            ->andWhere($qb->expr()->notIn('p.id', $this->getUserProjectsQb()->getDQL()))
            ->setParameter('user_id', $user->getId())
            ->getQuery();

        return $query->getResult();
    }
}