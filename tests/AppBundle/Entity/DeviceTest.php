<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Device;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

/**
 * Class DeviceTest
 * @package Tests\AppBundle\Entity
 *
 * @covers \AppBundle\Entity\Device
 */
class DeviceTest extends TestCase
{

    /**
     * @var Device $device
     */
    private $device;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $this->device = new Device();
    }

    /**
     *
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(\DateTime::class, $this->device->getCreatedDate());
        $this->assertInstanceOf(ArrayCollection::class, $this->device->getFlags());
    }

    /**
     *
     */
    public function testAttributes()
    {
        foreach (['id', 'serialNo', 'createdDate', 'lastModifiedDate'] as $attributeName) {
            $this->assertClassHasAttribute($attributeName, Device::class);
        }

        $this->assertIsIterable($this->device->getFlags());
        $this->assertNull($this->device->getSerialNo());
        $this->assertNull($this->device->getLastModifiedDate());

    }

}