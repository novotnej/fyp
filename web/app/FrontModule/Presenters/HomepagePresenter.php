<?php

namespace App\FrontModule\Presenters;

use App\DataGrids\DevicesDataGrid;
use App\DataGrids\QueuesDataGrid;

class HomepagePresenter extends BasePresenter {

    public function actionDefault() {
        //FIXME - only temporary for testing
        $this->redirect("Queue:");
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

}
