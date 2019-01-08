<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Device;
use AppBundle\Entity\Flag;
use PHPUnit\Framework\TestCase;

/**
 * Class FlagTest
 * @package Tests\AppBundle\Entity
 *
 * @covers \AppBundle\Entity\Flag
 */
class FlagTest extends TestCase
{

    /**
     * @var Device $device
     */
    private $device;

    /**
     * @var Flag $flag
     */
    private $flag;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        $this->device = new Device();
        $this->flag = new Flag();
    }

    /**
     *
     */
    public function testConstructor()
    {
        $this->assertInstanceOf(\DateTime::class, $this->flag->getCreatedDate());
    }

    /**
     *
     */
    public function testAttributes()
    {

        foreach (['id', 'name', 'createdDate', 'creatorIp', 'device'] as $attributeName) {
            $this->assertClassHasAttribute($attributeName, Flag::class);
        }

        $this->assertNull($this->flag->getDevice());
        $this->assertInstanceOf(Flag::class, $this->flag->setDevice($this->device));
        $this->assertInstanceOf(Device::class, $this->flag->getDevice());

    }

    /**
     * @throws \Exception
     */
    public function testIsFluentInterface()
    {
        foreach (['setDevice' => new Device(), 'setName' => 'dummy', 'setCreatorIp' => '255.255.255.255'] as $method => $value) {
            $this->assertInstanceOf(Flag::class, $this->flag->$method($value));
        }
    }

    /**
     * @covers Flag::transformIp
     * @covers Flag::transformIpFromLong
     */
    public function testIpTransform()
    {

        $this->flag->setCreatorIp('127.0.0.1');

        $this->assertIsString($this->flag->getCreatorIp());

        $this->flag->transformIp();

        $this->assertIsInt($this->flag->getCreatorIp());

        $this->flag->transformIpFromLong();

        $this->assertIsString($this->flag->getCreatorIp());


    }

}