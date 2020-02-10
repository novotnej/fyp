<?php

namespace App\FrontModule\Presenters;

use App\Components\Messages\ISendMessageFormFactory;
use App\Components\Messages\SendMessageForm;
use App\Components\Queues\IQueueEditFormFactory;
use App\DataGrids\QueuesDataGrid;
use App\Model\Queue;
use App\Services\QueueService;

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

    public function actionDelete($id) {
        //TODO - ACL
        $this->queue = $this->ormService->queues->getById($id);
        if (!$this->queue) {
            $this->setView("notFound");
        } else {
            $this->queueService->removeQueue($this->queue);
            $this->flashMessage("Queue ". $this->queue->name." was deleted.", "success");
            $this->redirect("default");
            $this->terminate();
        }
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

    /**
     * @return SendMessageForm
     */
    protected function createComponentSendMessageForm() {
        $factory = $this->sendMessageFormFactory->create();
        if ($this->queue) {
            $factory->setQueue($this->queue);
        }
        return $factory;
    }

    /** @var QueueService @inject */
    public $queueService;

    /** @var ISendMessageFormFactory @inject */
    public $sendMessageFormFactory;

    /** @var IQueueEditFormFactory @inject */
    public $queueComponentFactory;
}
