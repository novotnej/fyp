<?php

namespace App\FrontModule\Presenters;

use App\Components\Queues\IQueueEditFormFactory;
use App\DataGrids\QueuesDataGrid;
use App\Model\Queue;

class QueuePresenter extends BasePresenter {

    /** @var Queue */
    private $queue;

    public function actionDetail($id) {
        $this->queue = $this->ormService->queues->getById($id);
        if (!$this->queue) {
            $this->setView("notFound");
        }
    }

    public function renderDetail() {
        $this->template->queue = $this->queue;
    }

    public function actionEdit($id) {
        //TODO - ACL
        $this->queue = $this->ormService->queues->getById($id);
        if (!$this->queue) {
            $this->setView("notFound");
        }
    }

    public function renderEdit() {
        $this->template->queue = $this->queue;
    }

    /**
     * @return \App\Components\Queues\QueueEditForm
     */
    protected function createComponentAdd() {
        $component = $this->queueComponentFactory->create();
        return $component;
    }

    /**
     * @return \App\Components\Queues\QueueEditForm
     */
    protected function createComponentDetail() {
        $component = $this->queueComponentFactory->create();
        $component->setDetailItem($this->queue);
        return $component;
    }

    /**
     * @return \App\Components\Queues\QueueEditForm
     */
    protected function createComponentEdit() {
        $component = $this->queueComponentFactory->create();
        $component->setEditItem($this->queue);
        return $component;
    }

    /**
     * @param $name
     * @return QueuesDataGrid
     */
    protected function createComponentQueuesDataGrid($name) {
        return $this->createDataGrid("queues", $name);
    }

    /** @var IQueueEditFormFactory @inject */
    public $queueComponentFactory;
}
