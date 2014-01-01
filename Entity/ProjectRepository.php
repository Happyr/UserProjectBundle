<?php

namespace HappyR\UserProjectBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Eastit\Lego\OpusBundle\Entity\BaseOpus;
use Eastit\UserBundle\Entity\User;
use HappyR\IdentifierInterface;

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
     * @param IdentifierInterface &$user
     *
     * @return Project|null
     */
    public function findPrivateProject(IdentifierInterface &$user)
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
     * @param IdentifierInterface &$user
     *
     * @return array
     */
    public function findUserProjects(IdentifierInterface &$user)
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
     * @param IdentifierInterface &$user
     *
     * @return array
     */
    public function findNonUserProjects(IdentifierInterface &$user)
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