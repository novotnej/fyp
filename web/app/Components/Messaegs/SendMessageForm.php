<?php

namespace App\Components\Messages;

use App\Components\CommonComponent;
use App\Components\Forms\BaseForm;
use App\Model\Queue;
use App\Repositories\QueuesRepository;
use App\Services\QueueService;
use Nette;


class SendMessageForm extends CommonComponent {
    /** @var QueuesRepository */
    protected $queuesRepository;
    /** @var QueueService */
    protected $queueService;
    /** @var Queue */
    private $queue;

    const   VIEW_SEND_MESSAGE   = "send_message";

    private $view = self::VIEW_SEND_MESSAGE;

    /**
     * SendMessageForm constructor.
     * @param QueuesRepository $queuesRepository
     * @param QueueService $queueService
     */
    public function __construct(QueuesRepository $queuesRepository, QueueService $queueService) {
        parent::__construct();
        $this->queueService = $queueService;
        $this->queuesRepository = $queuesRepository;
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

    /**
     * @param Queue $queue
     */
    public function setQueue(Queue $queue) {
        $this->queue = $queue;
    }

    /**
     * @return BaseForm
     */
    protected function createComponentSendMessageForm() {
        $form = $this->createForm();
        $form->addText("content", "Message content");
        $form->addSubmit("submit", "Send Message");
        $form->onValidate[] = function(Nette\Application\UI\Form $form) {

        };

        $form->onSuccess[] = function(Nette\Application\UI\Form $form) {
            if ($this->queueService->publish($form->values->content, [$this->queue])) {
                $this->flashMessage("Message successfully sent.", "success");
            } else {
                $this->flashMessage("Message could not be sent. Please check logs or contact the administrator.", "danger");
            }
            $this->redirect("this");
        };
        return $form;
    }
}

interface ISendMessageFormFactory {
    /**
     * @return SendMessageForm
     */
    public function create();
}
