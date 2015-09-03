<?php


namespace Happyr\UserProjectBundle\Model;

use Happyr\IdentifierInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class ProjectMemberInterface
 *
 * @author Tobias Nyholm
 *
 */
interface ProjectMemberInterface extends IdentifierInterface, UserInterface
{
}