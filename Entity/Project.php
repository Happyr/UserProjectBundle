<?php

namespace HappyR\UserProjectBundle\Entity;

use HappyR\UserProjectBundle\Manager\PermissionManager;
use Doctrine\Common\Collections\ArrayCollection;
use HappyR\UserProjectBundle\Model\ProjectMemberInterface;
use HappyR\UserProjectBundle\Model\ProjectObjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Project
 *
 */
class Project
{
    /**
     * @var integer
     *
     */
    protected $id;

    /**
     * @var ArrayCollection
     *
     */
    protected $users;

    /**
     * @var ArrayCollection
     *
     */
    protected $objects;

    /**
     * @var bool public
     *
     * This indicates if it is a public project that users can request to join.
     * A private project has always one user and is pretty much hidden from everyone
     *
     *
     */
    protected $public = true;

    /**
     * @var array permissions
     *
     */
    protected $permissions;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="happyr.user.project.name.blank")
     * @Assert\Length(min=2,max=250,
     *              minMessage="happyr.user.project.name.short",maxMessage="happyr.user.project.name.long")
     */
    protected $name;

    /**
     * @var string
     *
     */
    protected $description;

    /**
     * @var \Datetime $createdAt
     *
     */
    protected $createdAt;

    /**
     * @var \Datetime $updatedAt
     *
     */
    protected $updatedAt;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->objects = new ArrayCollection();
        $this->permissions = array();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Set permissions for user
     *
     * @param ProjectMemberInterface &$user
     * @param string $mask
     *
     * @return $this
     */
    public function setPermission(ProjectMemberInterface &$user, $mask)
    {
        $this->permissions[$user->getId()] = $mask;

        return $this;
    }

    /**
     * Get the permissions for this user on this project
     *
     * @param ProjectMemberInterface $user
     *
     * @return string
     */
    public function getPermission(ProjectMemberInterface $user)
    {
        if (isset($this->permissions[$user->getId()])) {
            return $this->permissions[$user->getId()];
        }

        return 'NONE';
    }

    /**
     * Revoke persmissions for a user
     *
     * @param ProjectMemberInterface &$user
     *
     * @return $this
     */
    public function revokePermissions(ProjectMemberInterface &$user)
    {
        if (isset($this->permissions[$user->getId()])) {
            unset($this->permissions[$user->getId()]);
        }

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add user
     *
     * @param UserInterface &$user
     *
     * @return Project
     */
    public function addUser(UserInterface &$user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    /**
     * Remove an user
     *
     * @param UserInterface &$user
     *
     * @return boolean
     */
    public function removeUser(UserInterface &$user)
    {
        return $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Project
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add objects
     *
     * @param ProjectObjectInterface &$object
     *
     * @return $this
     */
    public function addObject(ProjectObjectInterface &$object)
    {
        if (!$this->objects->contains($object)) {
            $this->objects->add($object);
        }
        $object->setProject($this);

        return $this;
    }

    /**
     * Remove an object
     *
     * @param ProjectObjectInterface &$object
     *
     * @return bool
     */
    public function removeObject(ProjectObjectInterface &$object)
    {
        return $this->objects->removeElement($object);
    }

    /**
     * Get objects
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function getObjects()
    {
        return $this->objects;
    }

    /**
     *
     * @param boolean $public
     *
     * @return $this
     */
    public function setPublic($public)
    {
        $this->public = $public;

        return $this;
    }

    /**
     *
     * @return boolean
     */
    public function isPublic()
    {
        return $this->public;
    }

    /**
     * Update timestamp
     */
    public function updateUpdatedAt()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     *
     * @return \Datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     *
     * @return \Datetime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}