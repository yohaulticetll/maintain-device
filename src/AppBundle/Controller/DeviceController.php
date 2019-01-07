<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Device;
use AppBundle\Entity\Flag;
use AppBundle\Exception\FlagNotValidException;
use AppBundle\Validator\FlagValidatorInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Methods;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeviceController extends AbstractFOSRestController
{
    /**
     * @Methods\Get("/devices")
     */
    public function getDevicesAction()
    {

        $repo = $this->getDoctrine()->getRepository(Device::class);
        $data = $repo->findAll();

        $view = [];
        /**
         * @var Device $d
         */
        foreach ($data as $d) {
            $view[] = [
                'serial_number' => $d->getSerialNo(),
                'date_created' => $d->getCreatedDate(),
                'date_updated' => $d->getLastModifiedDate()
            ];
        }

        $view = $this->view($view, Response::HTTP_OK);

        return $this->handleView($view);

    }

    /**
     * @Methods\Get("/devices/{id}")
     *
     * @param $id
     * @return Response
     */
    public function getDeviceAction($id)
    {

        $repo = $this->getDoctrine()->getRepository(Device::class);
        $device = $repo->find($id);
        if (null === $device) {
            throw $this->createNotFoundException(sprintf("Resource with ID: %d not found", $id));
        }

        return $this->handleView($this->view([
            'serial_number' => $device->getSerialNo(),
            'date_created' => $device->getCreatedDate(),
            'date_updated' => $device->getLastModifiedDate(),
        ], Response::HTTP_OK));
    }

    /**
     * @Methods\Post("/devices")
     *
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return View|Response
     * @throws \Exception
     */
    public function addDeviceAction(Request $request, ValidatorInterface $validator)
    {

        $device = new Device();

        $device->setSerialNo($request->get('serialNumber'));

        $errors = $validator->validate($device);

        if (count($errors) === 0) {

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($device);
            $manager->flush();

            return $this->handleView($this->view($device, Response::HTTP_CREATED));
        }

        return $this->handleView($this->view($this->getEntityErrors($errors), Response::HTTP_BAD_REQUEST));


    }

    /**
     * @Methods\Delete("devices/{id}")
     *
     * @param $id
     * @return Response
     */
    public function deleteDeviceAction($id)
    {

        $manager = $this->getDoctrine()->getManager();

        $device = $manager->find(Device::class, $id);

        if (null !== $device) {
            $manager->remove($device);
            $manager->flush();

            return $this->handleView($this->view(sprintf("Device with ID: %d has been removed", $id), Response::HTTP_OK));
        }

        throw $this->createNotFoundException(sprintf("Resource with ID: %d not found", $id));

    }

    /**
     * @Methods\Get("/devices/{id}/flags")
     *
     * @param $id
     * @return Response
     */
    public function showFlagAction($id)
    {

        /**
         * @var Device $device
         */
        $device = $this->getDoctrine()->getRepository(Device::class)->find($id);

        if ($device !== null) {
            $flags = $device->getFlags();
            $view = [];
            foreach ($flags as $flag) {
                $view[] = [
                    'name' => $flag->getName(),
                    'created_by' => $flag->getCreatorIp(),
                    'created_date' => $flag->getCreatedDate(),
                ];
            }
            return $this->handleView($this->view($view, Response::HTTP_OK));
        }

        throw $this->createNotFoundException(sprintf("No device with ID: %d found", $id));

    }

    /**
     * @Methods\Post("/flags")
     *
     * @param Request $request
     * @param FlagValidatorInterface $flagValidator
     * @param ValidatorInterface $validator
     * @return Response
     * @throws \Exception
     */
    public function addFlagAction(Request $request, FlagValidatorInterface $flagValidator, ValidatorInterface $validator)
    {

        $manager = $this->getDoctrine()->getManager();

        $newFlag = $request->get('flag');
        $serialNumber = $request->get('serialNumber');

        if (empty($newFlag) || empty($serialNumber)) {
            return $this->handleView(
                $this->view("POSTed data is missing required parameter(s)", Response::HTTP_BAD_REQUEST)
            );
        }

        /**
         * @var Device $device
         */
        $device = $manager
            ->getRepository(Device::class)
            ->findOneBy(['serialNo' => $serialNumber]);

        if ($device === null) {
            throw $this->createNotFoundException(
                sprintf("Device with S/N: %s doesn't exist", $request->get('serialNumber'))
            );
        }

        $lastFlag = '';
        if (!$device->getFlags()->isEmpty()) {
            $lastFlag = $device->getFlags()->last()->getName();
        }

        try {
            $flagValidator->flagIsValid((string)$newFlag, $lastFlag);
        } catch (FlagNotValidException $e) {
            return $this->handleView($this->view($e->getMessage(), Response::HTTP_BAD_REQUEST));
        }

        $flag = new Flag();
        $flag->setName($newFlag)
            ->setDevice($device)
            ->setCreatorIp($request->getClientIp());

        if (($errors = $validator->validate($flag))->count() > 0) {
            return $this->handleView($this->view($this->getEntityErrors($errors), Response::HTTP_BAD_REQUEST));
        }

        $device
            ->addFlag($flag)
            ->setLastModifiedDate(new \DateTime());


        if (($errors = $validator->validate($flag))->count() > 0) {
            return $this->handleView($this->view($this->getEntityErrors($errors), Response::HTTP_BAD_REQUEST));
        }

        $manager->persist($device);
        $manager->flush();

        return $this->handleView($this->view(
            sprintf("Flag: %s has been added for device S/N: %s", $newFlag, $serialNumber),
            Response::HTTP_OK
        ));

    }

    /**
     * Collect error messages and return if any
     *
     * @param ConstraintViolationListInterface $errors
     * @return array
     */
    private function getEntityErrors(ConstraintViolationListInterface $errors): array
    {
        $messages = [];
        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()][] = $error->getMessage();
        }
        return $messages;
    }
}
