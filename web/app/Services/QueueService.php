<?php
namespace App\Services;

use App\Model\Queue;
use App\Repositories\QueuesRepository;
use \Gamee\RabbitMQ\Producer\Producer;

class QueueService extends CommonService {

    /** @var QueuesRepository */
    protected $queuesRepository;
    /** @var Producer  */
    protected $messagesProducer;

    /**
     * QueueService constructor.
     * @param QueuesRepository $queuesRepository
     * @param Producer $messagesProducer
     */
    public function __construct(QueuesRepository $queuesRepository, Producer $messagesProducer) {
        $this->queuesRepository = $queuesRepository;
        $this->messagesProducer = $messagesProducer;
    }

    /**
     * @param string $message
     * @param Queue[] $queues
     * @return bool
     */
    public function publish(string $message, array $queues) {
        $json = json_encode(['message' => $message]);
        $headers = [];
        $key = "";
        /** @var Queue $queue */
        foreach ($queues as $queue) {
            $key .= $queue->name.".";
        }
        $key = substr($key, 0, strlen($key) - 1); //remove the last dot
        \Tracy\Debugger::barDump($key);
        $this->messagesProducer->publish($json, $headers, $key);
        return true;
    }

    /**
     * @param $name
     * @return Queue
     */
    public function createQueue($name) {
        //TODO - create in Rabbit
        $queue = new Queue();
        $queue->name = $name;
        return $this->queuesRepository->persistAndFlush($queue);
    }

    /**
     * @param Queue $queue
     * @param $newName
     * @return Queue
     */
    public function changeQueue(Queue $queue, $newName) {
        //TODO - change in Rabbit
        $queue->name = $newName;
        return $this->queuesRepository->persistAndFlush($queue);
    }
}
