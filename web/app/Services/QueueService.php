<?php
namespace App\Services;

use App\Model\Queue;
use App\Repositories\QueuesRepository;

class QueueService extends CommonService {

    /** @var QueuesRepository */
    protected $queuesRepository;

    /**
     * QueueService constructor.
     * @param QueuesRepository $queuesRepository
     */
    public function __construct(QueuesRepository $queuesRepository) {
        $this->queuesRepository = $queuesRepository;
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
