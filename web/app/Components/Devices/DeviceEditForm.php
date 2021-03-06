<?php

namespace App\Components\Queues;

use App\Components\CommonComponent;
use App\Model\Device;
use App\Model\Queue;
use App\Repositories\DevicesRepository;
use App\Repositories\QueuesRepository;
use App\Services\QueueService;
use Nette;
use Nette\Utils\Strings;
use Tracy\Debugger;


class DeviceEditForm extends CommonComponent {
    /** @var DevicesRepository */
    protected $devicesRepository;
    /** @var QueuesRepository  */
    protected $queuesRepository;
    /** @var QueueService  */
    protected $queuesService;
    /** @var Device */
    protected $device;

    const   VIEW_EDIT   = "edit",
            VIEW_ADD    = "add",
            VIEW_DETAIL = "detail";

    private $view = self::VIEW_ADD;

    public function __construct(DevicesRepository $devicesRepository, QueuesRepository $queuesRepository, QueueService $queueService) {
        parent::__construct();
        $this->devicesRepository = $devicesRepository;
        $this->queuesRepository = $queuesRepository;
        $this->queuesService = $queueService;
    }

    public function setEditItem(Device $device) {
        $this->view = static::VIEW_EDIT;
        $this->device = $device;
    }

    public function setDetailItem(Device $device) {
        $this->view = static::VIEW_DETAIL;
        $this->device = $device;
    }

    public function render() {
        $template = parent::render();
        $template->view = $this->view;
        if ($this->device) {
            $template->device = $this->device;
        }
        $template->setFile(dirname(__FILE__) . "/". $this->view .".latte");
        $template->render();
    }

    protected function createComponentAddForm() {
        $form = $this->createForm();
        $form->addText("name", "Name");
        $form->addSubmit("submit", "Save changes");
        $form->onValidate[] = function(Nette\Application\UI\Form $form) {
            //check if not changing name to something that already exists
            $name = Strings::webalize($form->values->name);
            $duplicateName = $this->devicesRepository->findBy(["name" => $name])->count();
            if ($duplicateName > 0) {
                $form->addError("Device with name ". $name." is already in the system.");
            }
        };

        $form->onSuccess[] = function(Nette\Application\UI\Form $form) {
            $name = Strings::webalize($form->values->name);
            $secret = Nette\Utils\Random::generate(25);
            $device = new Device(["name" => $name, "secret" => $secret]);
            if ($device = $this->devicesRepository->persistAndFlush($device)) {

                $this->flashMessage("Device ".$name." successfully created.", "success");
                $this->presenter->redirect(":Front:Device:edit", $device->id);
            } else {
                $this->flashMessage("Unknown error occurred when creating a device. Please check logs or contact the administrator.", "danger");
                $this->redirect("this");
            }
        };
        return $form;
    }

    protected function createComponentEditForm() {
        $form = $this->createForm();
        $form->addText("name", "Name");

        $queues = $this->queuesRepository->findAll();
        $queueValues = [];
        $defaults = ["name" => $this->device->name, "queues" => []];
        /** @var Queue $queue */
        foreach ($queues as $queue) {
            $queueValues[$queue->id] = $queue->name;
            if ($this->device->queues->has($queue)) {
                $defaults["queues"][] = $queue->id;
            }
        }
        //TODO - do not show device-$id - this 
        $form->addCheckboxList("queues", "Queues", $queueValues);
        $form->setDefaults($defaults);
        $form->addSubmit("submit", "Save changes");
        $form->onValidate[] = function(Nette\Application\UI\Form $form) {
            //check if not changing name to something that already exists
            $name = Strings::webalize($form->values->name);
            $duplicateName = $this->devicesRepository->getBy(["name" => $name]);
            if ($duplicateName && $duplicateName->id != $this->device->id) {
                $form->addError("Device with name ". $name." is already in the system.");
            }
        };
        $form->onSuccess[] = function(Nette\Application\UI\Form $form) {
            $name = Strings::webalize($form->values->name);
            $this->device->name = $name;
            $selectedQueues = $this->queuesRepository->findById($form->values->queues);
            $queues = [];
            foreach ($selectedQueues as $selectedQueue) {
                $queues[] = $selectedQueue;
            }
            $this->device->queues->set($queues);
            //TODO - only send reload if the queues have changed
            $this->queuesService->sendReloadMessageToDevice($this->device);
            if ($this->devicesRepository->persistAndFlush($this->device)) {
                $this->flashMessage("Device ".$name." successfully modified.", "success");
            } else {
                $this->flashMessage("Unknown error occurred when modifying a device. Please check logs or contact the administrator.", "danger");
            }
            $this->redirect("this");
        };
        return $form;
    }
}

interface IDeviceEditFormFactory {
    /**
     * @return DeviceEditForm
     */
    public function create();
}
