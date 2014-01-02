<?php

namespace HappyR\UserProjectBundle\Manager;

use HappyR\UserProjectBundle\Entity\Project;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use HappyR\UserProjectBundle\Model\ProjectObjectInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;

/**
 * Class PermissionManager
 *
 * @author Tobias Nyholm
 *
 * This class could be considered a part of the Project Manager. This is handles permissions within the project and
 * adding/removing users and objectes. If you want to add a User to the Project, use the ProjectManager
 *
 */
class PermissionManager extends BaseAclManager
{
    /**
     * @var AclProviderInterface aclProvider
     *
     *
     */
    protected $aclProvider;

    /*
     * @var array $validMasks
     *
     * An array with valid masks. From the least generous to most
     *
     * VIEW - View details about an object and the project
     * EDIT - Change objectes etc
     * CREATE - Create new objectes
     * DELETE - This user may remove an object
     * MASTER - This is the administrator of the project. He allowed to add users, delete users and change permissions
     *
     */
    public static $validMasks = array('VIEW', 'EDIT', 'CREATE', 'DELETE', 'MASTER');

    /**
     * @param AclProviderInterface $aclProvider
     */
    public function __construct(AclProviderInterface $aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }

    /**
     * Add an object to a project. This updates the permissions for all users in the project
     *
     * @param Project &$project
     * @param ProjectObjectInterface &$object
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addObject(Project &$project, ProjectObjectInterface &$object)
    {
        $project->addObject($object);
        $users = $project->getUsers();

        foreach ($users as $user) {
            $bitMask = $this->getBitMask($project->getPermission($user));
            $this->addUserAce($object, $user, $bitMask);
        }

        return $this;
    }

    /**
     * Remove an object from the project
     *
     * Remember to persist the $object as well. Doctrine can't find the object since we cut the relation.
     *
     * @param Project &$project
     * @param ProjectObjectInterface &$object
     *
     * @return $this
     */
    public function removeObject(Project &$project, ProjectObjectInterface &$object)
    {
        $project->removeObject($object);
        $users = $project->getUsers();

        foreach ($users as $user) {
            $this->removeUserAce($object, $user);
        }

        return $this;
    }

    /**
     * Add a user to a project. This gives the user the proper permissions to all objectes in the project
     *
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     * @param string $mask
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addUser(Project &$project, ProjectMemberInterface &$user, $mask = 'VIEW')
    {
        $project->addUser($user);

        return $this->changePermissions($project, $user, $mask);
    }

    /**
     * Remove a user from the project
     *
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     *
     * @return $this
     */
    public function removeUser(Project &$project, ProjectMemberInterface &$user)
    {
        $project->removeUser($user);

        return $this->revokePermissions($project, $user);
    }

    /**
     * Change a users privileges in a project.
     *
     *
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     * @param string $mask
     *
     * @return $this;
     *
     * @throws \ErrorException
     */
    public function changePermissions(Project &$project, ProjectMemberInterface &$user, $mask)
    {
        $mask = strtoupper($mask);
        $validMasks = self::$validMasks;
        $validMasks[] = 'REVOKE';

        if (!in_array($mask, $validMasks)) {
            throw new \ErrorException(
                sprintf(
                    "The string '%s' is not a valid mask. Valid masks are (" . implode(',', $validMasks) . ").",
                    $mask
                )
            );
        }

        if ($mask == 'REVOKE') {
            return $this->revokePermissions($project, $user);
        }

        /*
         * Save mask in the user permissions
         */
        $bitMask = $this->getBitMask($mask);

        $project->setPermission($user, $mask);
        $this->addUserAce($project, $user, $bitMask);

        /*
         * Add some permissions on the $project->getOpuses for the $user
         */
        $objectes = $project->getObjects();
        foreach ($objectes as $object) {
            $this->addUserAce($object, $user, $bitMask);
        }

        return $this;
    }

    /**
     * Remove permissions for the user
     *
     * @param Project &$project
     * @param ProjectMemberInterface &$user
     *
     * @return $this
     */
    private function revokePermissions(Project &$project, ProjectMemberInterface &$user)
    {
        $project->revokePermissions($user);

        //remove project ace
        $this->removeUserAce($project, $user);

        //remove ace for each object
        $objectes = $project->getObjects();
        foreach ($objectes as $object) {
            $this->removeUserAce($object, $user);
        }

        return $this;
    }

    /**
     * Get the bitmask from a string mask
     *
     * @param string $mask
     *
     * @return integer
     */
    protected function getBitMask($mask)
    {
        $strMask = "MASK_" . $mask;

        return constant("Symfony\Component\Security\Acl\Permission\MaskBuilder::$strMask");
    }
}
