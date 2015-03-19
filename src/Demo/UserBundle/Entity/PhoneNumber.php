<?php

namespace Demo\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PhoneNumber
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class PhoneNumber {
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $number;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->getNumber();
    }
}
