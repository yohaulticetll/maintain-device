<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Class Device
 *
 * @ORM\Entity
 * @ORM\Table(name="device")
 * @UniqueEntity("serialNo")
 * @ORM\HasLifecycleCallbacks
 */
class Device
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="serial_no", type="string", length=100, unique=true, nullable=false)
     * @Assert\Regex("/^\w+$/")
     * @Assert\Length(min=5,max=50)
     * @assert\NotBlank()
     */
    private $serialNo;

    /**
     * @var
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $createdDate;

    /**
     * @var
     *
     * @ORM\Column(name="date_modified", type="datetime", nullable=true)
     */
    private $lastModifiedDate;

    /**
     * One device has many flags
     * @ORM\OneToMany(targetEntity="Flag", mappedBy="device", cascade={"persist", "remove"})
     */
    private $flags;

    /**
     * Device constructor.
     * @throws \Exception
     */
    public function __construct()
    {
        $this->createdDate = new \DateTime();
        $this->flags = new ArrayCollection();
    }

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @throws \Exception
     */
    public function updateModifiedDate(){
        $this->setLastModifiedDate(new \DateTime());
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
     * Set serialNo.
     *
     * @param string $serialNo
     *
     * @return Device
     */
    public function setSerialNo($serialNo)
    {
        $this->serialNo = $serialNo;

        return $this;
    }

    /**
     * Get serialNo.
     *
     * @return string
     */
    public function getSerialNo()
    {
        return $this->serialNo;
    }

    /**
     * Set createdDate.
     *
     * @param \DateTime $createdDate
     *
     * @return Device
     */
    public function setCreatedDate($createdDate)
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
     * Set lastModifiedDate.
     *
     * @param \DateTime $lastModifiedDate
     *
     * @return Device
     */
    public function setLastModifiedDate($lastModifiedDate)
    {
        $this->lastModifiedDate = $lastModifiedDate;

        return $this;
    }

    /**
     * Get lastModifiedDate.
     *
     * @return \DateTime
     */
    public function getLastModifiedDate()
    {
        return $this->lastModifiedDate;
    }

    /**
     * Add flag.
     *
     * @param \AppBundle\Entity\Flag $flag
     *
     * @return Device
     */
    public function addFlag(Flag $flag)
    {
        $this->flags[] = $flag;
        return $this;
    }

    /**
     * Remove flag.
     *
     * @param \AppBundle\Entity\Flag $flag
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    private function removeFlag(Flag $flag)
    {
        return $this->flags->removeElement($flag);
    }

    /**
     * Get flags.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFlags()
    {
        return $this->flags;
    }

}