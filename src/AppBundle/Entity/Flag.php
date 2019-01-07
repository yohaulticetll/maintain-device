<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="flag")
 * @UniqueEntity("name")
 */
class Flag
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true, nullable=false)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    private $createdDate;

    /**
     * @var
     *
     * @ORM\Column(name="creator_ip", type="integer")
     * @Assert\Ip
     */
    private $creatorIp;

    /**
     * One device has many flags
     * @ORM\ManyToOne(targetEntity="Device", inversedBy="flags")
     * @ORM\JoinColumn(name="device_id", referencedColumnName="id")
     */
    private $device;

    public function __construct()
    {
        $this->createdDate = new \DateTime();

    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Flag
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdDate.
     *
     * @param \DateTime $createdDate
     *
     * @return Flag
     */
    private function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate.
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * Set creatorIp.
     *
     * @param int $creatorIp
     *
     * @return Flag
     */
    public function setCreatorIp($creatorIp)
    {
        $this->creatorIp = ip2long($creatorIp);

        return $this;
    }

    /**
     * Get creatorIp.
     *
     * @return int
     */
    public function getCreatorIp()
    {
        return long2ip($this->creatorIp);
    }

    /**
     * Set device.
     *
     * @param \AppBundle\Entity\Device|null $device
     *
     * @return Flag
     */
    public function setDevice(\AppBundle\Entity\Device $device = null)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get device.
     *
     * @return \AppBundle\Entity\Device|null
     */
    public function getDevice()
    {
        return $this->device;
    }



}