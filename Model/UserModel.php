<?php

namespace HappyR\UserProjectBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserModel
 *
 * @author Tobias Nyholm
 *
 */
class UserModel
{
    /**
     * @var User user
     *
     * @Assert\NotNull
     */
    protected $user;

    /**
     *
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
