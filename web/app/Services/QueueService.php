<?php
namespace App\Services;

use App\Model\Device;
use App\Model\Queue;
use App\Repositories\QueuesRepository;
use \Gamee\RabbitMQ\Producer\Producer;

class QueueService extends CommonService {

    const   MSG_TYPE_NORMAL   = "normal",
            MSG_TYPE_RELOAD   = "reload";


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
     * @param string $type
     * @return bool
     */
    public function publish(string $message, array $queues, string $type = self::MSG_TYPE_NORMAL) {
        $json = json_encode([
            "message" => $message,
            "type" => $type
        ]);

        $headers = [];
        $key = "";
        /** @var Queue $queue */
        foreach ($queues as $queue) {
            $key .= $queue->name.".";
        }
        $key = substr($key, 0, strlen($key) - 1); //remove the last dot
        $this->messagesProducer->publish($json, $headers, $key);
        return true;
    }

    /**
     * @param Queue $queue
     */
    public function removeQueue(Queue $queue) {
        if ($queue) {
            $this->queuesRepository->removeAndFlush($queue);
            //notify subscribed devices their configuration has changed
            $this->publish("", [$queue], static::MSG_TYPE_RELOAD);
        }
    }

    /**
     * @param Device $device
     */
    public function removeDeviceQueue(Device $device) {
        if ($device) {
            $name = "device-". $device->id;
            $queue = $this->queuesRepository->getBy(["name" => $name]);
            if ($queue) {
                if (count($queue->devices) == 1) { //only remove the device queue if the device is alone listening to it
                    $this->removeQueue($queue);
                }
            }
        }
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
