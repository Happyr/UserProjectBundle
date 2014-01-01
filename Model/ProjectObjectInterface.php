<?php


namespace HappyR\UserProjectBundle\Model;

use HappyR\IdentifierInterface;
use HappyR\UserProjectBundle\Entity\Project;

/**
 * Class ProjectObjectInterface
 *
 * @author Tobias Nyholm
 *
 */
interface ProjectObjectInterface extends IdentifierInterface
{
    public function getProject();
    public function setProject(Project $project);
}