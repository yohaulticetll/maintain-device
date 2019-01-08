<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Flag;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\Console\Input\StringInput;

/**
 * Class DeviceControllerTest
 * @package Tests\AppBundle\Controller
 */
class DeviceControllerTest extends WebTestCase
{
    /**
     * @var Client $client
     */
    protected $client;

    /**
     * @var Application $app
     */
    protected $app;

    /**
     * @var EntityManager $em
     */
    protected $em;

    const API_GET_DEVICES_URL = '/api/devices';

    const API_GET_DEVICE_URL = '/api/devices/%d';

    const API_POST_DEVICE_URL = '/api/devices';

    const API_DELETE_DEVICE_URL = '/api/devices/%d';

    const API_POST_FLAG_URL = '/api/flags';

    const API_GET_FLAG_URL = '/api/devices/%d/flags';

    /**
     * set up test db commands
     *
     * @var array
     */
    protected $onSetUp = [
        'doctrine:database:drop --force --quiet',
        'doctrine:database:create --quiet',
        'doctrine:schema:create --quiet'
    ];

    /**
     * clear test db command
     *
     * @var array
     */
    protected $onTearDown = [
        'doctrine:database:drop --force --quiet'
    ];

    /**
     * set up entity manager and test database. init http client
     *
     * @throws \Exception
     */
    public function setUp()
    {
        $this->client = static::createClient();
        $this->app = new Application($this->client->getKernel());
        $this->app->setAutoExit(false);

        foreach ($this->onSetUp as $command) {
            $this->app->run(new StringInput($command));
        }

        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()->get('doctrine')->getManager();

    }

    /**
     * @covers \AppBundle\Controller\DeviceController::getDevicesAction
     * @covers \AppBundle\Controller\DeviceController::getDeviceAction
     */
    public function testGetDevices()
    {
        $serials = [];
        $ids = [];
        foreach ($this->deviceProvider() as list($serial, $valid)) {
            if ($valid === true) {
                $this->client->request('POST', self::API_POST_DEVICE_URL, ['serialNumber' => $serial]);
                $this->assertEquals(201, $this->client->getResponse()->getStatusCode());
                $serials[] = $serial;
                $ids[] = json_decode($this->client->getResponse()->getContent())->id;
            }
        }

        $this->client->request("GET", self::API_GET_DEVICES_URL);


        $result = json_decode($this->client->getResponse()->getContent());

        $this->assertCount(sizeof($serials), $result);

        foreach (['serial_number', 'date_created', 'date_updated'] as $attr) {
            foreach ($result as $obj) {
                $this->assertObjectHasAttribute($attr, $obj);
            }
        }

        foreach ($ids as $id) {
            $this->client->request("GET", sprintf(self::API_GET_DEVICE_URL, $id));

            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        }

        $this->client->request("GET", sprintf(self::API_GET_DEVICE_URL, 99999));

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

    }

    /**
     * @covers \AppBundle\Controller\DeviceController::deleteDeviceAction
     */
    public function testDeleteDevice()
    {
        $ids = [];
        foreach ($this->flagProvider() as list($serial, $flags, $valid)) {
            if ($valid === true) {
                $this->client->request('POST', self::API_POST_DEVICE_URL, ['serialNumber' => $serial]);
                $ids[] = json_decode($this->client->getResponse()->getContent())->id;
                foreach ($flags as $flag) {
                    $this->client->request("POST", self::API_POST_FLAG_URL, ['serialNumber' => $serial, 'flag' => $flag]);
                }

            }
        }

        foreach ($ids as $id) {
            $flagsCountPre = (int)$this->em->getRepository(Flag::class)->findBy(['device' => $id]);
            $this->client->request("DELETE", sprintf(self::API_DELETE_DEVICE_URL, $id));
            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
            $flagsCountPost = (int)$this->em->getRepository(Flag::class)->findBy(['device' => $id]);
            $this->assertTrue($flagsCountPre !== $flagsCountPost);

            $this->client->request("DELETE", sprintf(self::API_DELETE_DEVICE_URL, $id));
            $this->assertEquals(404, $this->client->getResponse()->getStatusCode());

        }


    }

    /**
     * @dataProvider deviceProvider
     * @covers       \AppBundle\Controller\DeviceController::addDeviceAction
     *
     * @param $serialNo
     * @param $valid
     */
    public function testAddDevice($serialNo, $valid)
    {
        $this->client->request('POST', self::API_POST_DEVICE_URL, ['serialNumber' => $serialNo]);
        $this->assertEquals($valid ? 201 : 400, $this->client->getResponse()->getStatusCode());

        //duplicate request to test unique constraint
        if ($valid === true) {
            $this->client->request('POST', self::API_POST_DEVICE_URL, ['serialNumber' => $serialNo]);
            $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        }
    }

    /**
     * @dataProvider flagProvider
     * @covers       \AppBundle\Controller\DeviceController::addFlagAction
     *
     * @param $serialNo
     * @param $flags
     * @param $valid
     */
    public function testAddFlag($serialNo, $flags, $valid)
    {

        $this->client->request('POST', self::API_POST_DEVICE_URL, ['serialNumber' => $serialNo]);

        foreach ($flags as $flag) {

            $this->client->request('POST', self::API_POST_FLAG_URL, [
                'serialNumber' => $serialNo,
                'flag' => $flag
            ]);

            $this->assertEquals($valid ? 201 : 400, $this->client->getResponse()->getStatusCode());

        }

    }

    /**
     * @covers \AppBundle\Controller\DeviceController::addFlagAction
     */
    public function testAddFlag404()
    {
        $this->client->request('POST', self::API_POST_FLAG_URL, [
            'serialNumber' => 'SNNOTEXISTING',
            'flag' => 'dekompletacja_rozpakowywanie'
        ]);

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @covers \AppBundle\Controller\DeviceController::getFlagAction
     */
    public function testShowFlag()
    {

        $flags = [];
        foreach ($this->flagProvider() as list($serial, $flagsToSet, $valid)) {
            if ($valid === true) {
                $this->client->request('POST', self::API_POST_DEVICE_URL, ['serialNumber' => $serial]);
                $id = json_decode($this->client->getResponse()->getContent())->id;
                $flags[$id] = [];
                foreach ($flagsToSet as $flag) {
                    $this->client->request("POST", self::API_POST_FLAG_URL, ['serialNumber' => $serial, 'flag' => $flag]);
                    $flags[$id][] = $flag;
                }

            }
        }

        foreach ($flags as $id => $flagsSet) {
            $this->client->request("GET", sprintf(self::API_GET_FLAG_URL, $id));

            $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

            $this->assertCount(sizeof($flagsSet), json_decode($this->client->getResponse()->getContent()));
        }

        $this->client->request("GET", sprintf(self::API_GET_FLAG_URL, 999999));

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @dataProvider urlProvider
     *
     * @param $method
     * @param $url
     * @param $expectedCode
     */
    public function testValidUrls($method, $url, $expectedCode)
    {
        $this->client->request($method, $url);
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return array
     */
    public function deviceProvider()
    {
        return [
            ['1', false],
            [null, false],
            ['asdas%^&%', false],
            [121, false],

            ['SS782', true],
            ['ASD875785', true],
            ['AASSDDDBBB', true],
            ['SN677676ASD', true],
        ];

    }

    /**
     * @return array
     */
    public function flagProvider()
    {
        return [
            ['SN786', [''], false],
            ['', [''], false],
            ['', ['dekompletacja_rozpakowywanie'], false],
            ['SN712', ['not_existing_flag'], false],
            ['SN725', ['testowanie_uszkodzony'], false],
            ['SN721', ['wymiana_obudowy'], false], //bad order

            ['SN992', ['dekompletacja_rozpakowywanie', 'testowanie_sprawny', 'czyszczenie', 'pakowanie'], true],
            ['SN892', ['dekompletacja_rozpakowywanie', 'testowanie_sprawny', 'wymiana_obudowy', 'pakowanie'], true],
            ['SN995', ['dekompletacja_rozpakowywanie', 'testowanie_uszkodzony', 'pakowanie_uszkodzony'], true],
            ['SN195', ['dekompletacja_rozpakowywanie', 'testowanie_uszkodzony'], true],
            ['SN922', ['dekompletacja_rozpakowywanie'], true],

        ];
    }

    /**
     * @return array
     */
    public function urlProvider()
    {
        return [
            ['GET', self::API_GET_DEVICES_URL, 200],
            ['POST', self::API_POST_FLAG_URL, 400],
            ['PUT', self::API_GET_DEVICES_URL, 405],
            ['GET', '/api/not-valid-url', 404],
            ['DELETE', '/api/flags', 405],
            ['GET', '/api', 404]
        ];
    }

    /**
     * Tear down routing after each test. Clear test database and unset entity manager
     *
     * @throws \Exception
     */
    public function tearDown()
    {
        foreach ($this->onTearDown as $command) {
            $this->app->run(new StringInput($command));
        }

        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }


}