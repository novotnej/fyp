<?php

namespace App\Components\Queues;

use App\Components\CommonComponent;
use App\Model\Device;
use App\Repositories\DevicesRepository;
use Nette;
use Nette\Utils\Strings;


class DeviceEditForm extends CommonComponent {
    /** @var DevicesRepository */
    protected $devicesRepository;
    /** @var Device */
    protected $device;

    const   VIEW_EDIT   = "edit",
            VIEW_ADD    = "add",
            VIEW_DETAIL = "detail";

    private $view = self::VIEW_ADD;

    public function __construct(DevicesRepository $devicesRepository) {
        parent::__construct();
        $this->devicesRepository = $devicesRepository;
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
            $device = new Device(["name" => $name]);
            if ($this->devicesRepository->persistAndFlush($device)) {
                $this->flashMessage("Device ".$name." successfully created.", "success");
            } else {
                $this->flashMessage("Unknown error occurred when creating a device. Please check logs or contact the administrator.", "danger");
            }
            $this->redirect("this");
        };
        return $form;
    }

    protected function createComponentEditForm() {
        $form = $this->createForm();
        $form->addText("name", "Name")->setDefaultValue($this->device->name);
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
