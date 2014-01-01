<?php


namespace HappyR\UserProjectBundle\Model;

use HappyR\IdentifierInterface;
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