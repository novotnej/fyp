<?php

namespace App\FrontModule\Presenters;

use App\DataGrids\DevicesDataGrid;
use App\DataGrids\QueuesDataGrid;

class HomepagePresenter extends BasePresenter {

    public function actionDefault() {

    }

    public function renderDefault() {
    }

    /**
     * @param $name
     * @return DevicesDataGrid
     */
    protected function createComponentDevicesDataGrid($name) {
        return $this->createDataGrid('devices', $name);
    }

    /**
     * @param $name
     * @return QueuesDataGrid
     */
    protected function createComponentQueuesDataGrid($name) {
        return $this->createDataGrid("queues", $name);
    }
}
