<?php

namespace Happyr\UserProjectBundle\Manager;

use Symfony\Component\Security\Acl\Exception\AclAlreadyExistsException;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Acl\Model\AclInterface;

/**
 * Class BaseAclManager.
 *
 * @author Tobias Nyholm
 */
abstract class BaseAclManager
{
    /**
     * @var MutableAclProviderInterface aclProvider
     */
    protected $aclProvider;

    /**
     * @param MutableAclProviderInterface $aclProvider
     */
    public function __construct(MutableAclProviderInterface $aclProvider)
    {
        $this->aclProvider = $aclProvider;
    }

    /**
     * Return a ACL for the object.
     *
     * @param mixed $object
     *
     * @return \Symfony\Component\Security\Acl\Domain\Acl
     */
    protected function getObjectAcl($object)
    {
        $objectIdentity = ObjectIdentity::fromDomainObject($object);
        try {
            $acl = $this->aclProvider->createAcl($objectIdentity);
        } catch (AclAlreadyExistsException $e) {
            //find acl
            $acl = $this->aclProvider->findAcl($objectIdentity);
        }

        return $acl;
    }

    /**
     * Get the user security identity.
     *
     * @param UserInterface $user
     *
     * @return UserSecurityIdentity
     */
    protected function getUserIdentity(UserInterface $user)
    {
        $identity = UserSecurityIdentity::fromAccount($user);

        return $identity;
    }

    /**
     * Add a user to the object and set the permission mask.
     *
     * Thus function persists the object but does not flush
     *
     * @param mixed         $object
     * @param UserInterface $user
     * @param int           $permissionMask
     */
    protected function addUserAce($object, UserInterface $user, $permissionMask)
    {
        $securityIdentity = $this->getUserIdentity($user);
        $acl = $this->getObjectAcl($object);

        $aceIndex = $this->getAceIndex($acl, $securityIdentity);
        if (false !== $aceIndex) {
            $acl->updateObjectAce($aceIndex, $permissionMask);
        } else {
            //there is no access control entity for this user in this acl,
            //Create one
            $acl->insertObjectAce($securityIdentity, $permissionMask);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Delete the access control entity for this user on this object.
     *
     * @param mixed         $object
     * @param UserInterface $user
     */
    protected function removeUserAce($object, UserInterface $user)
    {
        $securityIdentity = $this->getUserIdentity($user);
        $acl = $this->getObjectAcl($object);

        $aceIndex = $this->getAceIndex($acl, $securityIdentity);
        if (false !== $aceIndex) {
            //remove
            $acl->deleteObjectAce($aceIndex);
        }

        $this->aclProvider->updateAcl($acl);
    }

    /**
     * Return the index of the Ace for the $securityIdentity in the $acl.
     *
     * @param AclInterface         $acl
     * @param UserSecurityIdentity $securityIdentity
     *
     * @return int|bool false if not found
     */
    protected function getAceIndex(AclInterface $acl, UserSecurityIdentity $securityIdentity)
    {
        //gets the aces
        $aces = $acl->getObjectAces();

        //we have to check every Ace and find the one related to our
        foreach ($aces as $index => $ace) {
            if ($ace->getSecurityIdentity() == $securityIdentity) {
                return $index;
            }
        }

        return false;
    }
}
