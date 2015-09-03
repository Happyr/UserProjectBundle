<?php


namespace Happyr\UserProjectBundle\Model;

use Happyr\IdentifierInterface;
use Happyr\UserProjectBundle\Entity\Project;

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