<?php


namespace Happyr\UserProjectBundle\Manager;

use Happyr\UserProjectBundle\Entity\Project;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class SecurityManager
 *
 * @author Tobias Nyholm
 *
 */
class SecurityManager
{
    /**
     * @var SecurityContextInterface context
     *
     */
    protected $context;

    /**
     * Default constructor
     *
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->context = $securityContext;
    }

    /**
     * Does the current user have $mask permissions on $object?
     *
     * @param string $mask can be VIEW, EDIT, DELETE, OPERATOR etc
     * @param mixed &$object any entity
     *
     * @return boolean
     */
    public function userIsGranted($mask, &$object)
    {
        // check for access with ACL
        return $this->context->isGranted($mask, $object);
    }

    /**
     * Throws a AccessDeniedException if the user is not granted the $mask on the Object.
     *
     * @param string $mask can be VIEW, EDIT, DELETE, OPERATOR etc
     * @param mixed &$object any entity
     *
     * @return bool
     * @throws AccessDeniedHttpException
     */
    public function verifyUserIsGranted($mask, &$object)
    {
        if ($this->userIsGranted($mask, $object)) {
            return true;
        }

        throw new AccessDeniedHttpException(sprintf('You have no privileges to "%s" this resource.', strtolower($mask)));
    }

    /**
     * Throws a AccessDeniedException if the project is not public
     *
     * @param Project $project
     *
     * @return bool
     * @throws AccessDeniedHttpException
     */
    public function verifyProjectIsPublic(Project &$project)
    {
        if ($project->isPublic()) {
            return true;
        }

        throw new AccessDeniedHttpException('This is not a public project.');
    }
}