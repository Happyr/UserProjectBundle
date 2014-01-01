<?php

namespace HappyR\UserProjectBundle\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ObjectModel
 *
 * @author Tobias Nyholm
 *
 */
class ObjectModel
{
    /**
     * @var Object opus
     *
     * @Assert\NotNull
     */
    protected $object;

    /**
     *
     * @param Object $object
     *
     * @return $this
     */
    public function setObject(Object $object)
    {
        $this->object = $object;

        return $this;
    }

    /**
     *
     * @return Object
     */
    public function getObject()
    {
        return $this->object;
    }
}
