<?php

namespace Happyr\UserProjectBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ProjectMemberInterface.
 *
 * @author Tobias Nyholm
 */
interface ProjectMemberInterface extends UserInterface
{
    public function getId();
}
