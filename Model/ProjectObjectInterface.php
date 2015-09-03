<?php

namespace Happyr\UserProjectBundle\Model;

use Happyr\UserProjectBundle\Entity\Project;

/**
 * Class ProjectObjectInterface.
 *
 * @author Tobias Nyholm
 */
interface ProjectObjectInterface
{
    public function getId();
    public function getProject();
    public function setProject(Project $project);
}
