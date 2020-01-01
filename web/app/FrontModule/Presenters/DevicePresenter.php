<?php

namespace App\FrontModule\Presenters;

use App\Components\Queues\DeviceEditForm;
use App\Components\Queues\IDeviceEditFormFactory;
use App\Components\Queues\IQueueEditFormFactory;
use App\DataGrids\QueuesDataGrid;
use App\Model\Device;

class DevicePresenter extends BasePresenter {

    /** @var Device */
    private $device;

    public function actionDetail($id) {
        $this->device = $this->ormService->devices->getById($id);
        if (!$this->device) {
            $this->setView("notFound");
        }
    }

    public function renderDetail() {
        $this->template->device = $this->device;
    }

    public function actionEdit($id) {
        //TODO - ACL
        $this->device = $this->ormService->devices->getById($id);
        if (!$this->device) {
            $this->setView("notFound");
        }
    }

    public function renderEdit() {
        $this->template->device = $this->device;
    }

    /**
     * @return DeviceEditForm
     */
    protected function createComponentAdd() {
        $component = $this->deviceComponentFactory->create();
        return $component;
    }

    /**
     * @return DeviceEditForm
     */
    protected function createComponentDetail() {
        $component = $this->deviceComponentFactory->create();
        $component->setDetailItem($this->device);
        return $component;
    }

    /**
     * @return DeviceEditForm
     */
    protected function createComponentEdit() {
        $component = $this->deviceComponentFactory->create();
        $component->setEditItem($this->device);
        return $component;
    }

    /**
     * @param $name
     * @return QueuesDataGrid
     */
    protected function createComponentDevicesDataGrid($name) {
        return $this->createDataGrid("devices", $name);
    }

    /** @var IDeviceEditFormFactory @inject */
    public $deviceComponentFactory;
}
