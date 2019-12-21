<?php

namespace App\Components\Queues;

use App\Components\CommonComponent;
use App\Model\Queue;
use App\Repositories\QueuesRepository;
use App\Services\QueueService;
use Nette;


class QueueEditForm extends CommonComponent {
    /** @var QueuesRepository */
    protected $queuesRepository;
    /** @var QueueService */
    protected $queueService;
    /** @var Queue */
    private $queue;

    const   VIEW_EDIT   = "edit",
            VIEW_ADD    = "add",
            VIEW_DETAIL = "detail";

    private $view = self::VIEW_ADD;

    public function __construct(QueuesRepository $queuesRepository, QueueService $queueService) {
        parent::__construct();
        $this->queueService = $queueService;
        $this->queuesRepository = $queuesRepository;
    }

    public function setEditItem(Queue $queue) {
        $this->view = static::VIEW_EDIT;
        $this->queue = $queue;
    }

    public function setDetailItem(Queue $queue) {
        $this->view = static::VIEW_DETAIL;
        $this->queue = $queue;
    }

    public function render() {
        $template = parent::render();
        $template->view = $this->view;
        if ($this->queue) {
            $template->queue = $this->queue;
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
            $name = \Nette\Utils\Strings::webalize($form->values->name);
            $duplicateName = $this->queuesRepository->findBy(["name" => $name])->count();
            if ($duplicateName > 0) {
                $form->addError("Queue with name ". $name." is already in the system.");
            }
        };

        $form->onSuccess[] = function(Nette\Application\UI\Form $form) {
            $name = \Nette\Utils\Strings::webalize($form->values->name);
            if ($this->queueService->createQueue($name)) {
                $this->flashMessage("Queue ".$name." successfully created.", "success");
            } else {
                $this->flashMessage("Unknown error occurred when creating a queue. Please check logs or contact the administrator.", "danger");
            }
            $this->redirect("this");
        };
        return $form;
    }

    protected function createComponentEditForm() {
        $form = $this->createForm();
        $form->addText("name", "Name")->setDefaultValue($this->queue->name);
        $form->addSubmit("submit", "Save changes");
        $form->onValidate[] = function(Nette\Application\UI\Form $form) {
            //check if not changing name to something that already exists
            $name = \Nette\Utils\Strings::webalize($form->values->name);
            $duplicateName = $this->queuesRepository->getBy(["name" => $name]);
            if ($duplicateName && $duplicateName->id != $this->queue->id) {
                $form->addError("Queue with name ". $name." is already in the system.");
            }
        };
        $form->onSuccess[] = function(Nette\Application\UI\Form $form) {
            $name = \Nette\Utils\Strings::webalize($form->values->name);
            if ($this->queueService->changeQueue($this->queue, $name)) {
                $this->flashMessage("Queue ".$name." successfully modified.", "success");
            } else {
                $this->flashMessage("Unknown error occurred when modifying a queue. Please check logs or contact the administrator.", "danger");
            }
            $this->redirect("this");
        };
        return $form;
    }
}

interface IQueueEditFormFactory {
    /**
     * @return QueueEditForm
     */
    public function create();
}
