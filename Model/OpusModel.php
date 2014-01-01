<?php

namespace HappyR\UserProjectBundle\Model;

use Eastit\Lego\OpusBundle\Entity\Opus;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserModel
 *
 * @author Tobias Nyholm
 *
 */
class OpusModel
{
    /**
     * @var Opus opus
     *
     * @Assert\NotNull
     */
    protected $opus;

    /**
     *
     * @param Opus $opus
     *
     * @return $this
     */
    public function setOpus(Opus $opus)
    {
        $this->opus = $opus;

        return $this;
    }

    /**
     *
     * @return Opus
     */
    public function getOpus()
    {
        return $this->opus;
    }
}
