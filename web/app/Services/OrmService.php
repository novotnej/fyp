<?php
namespace App\Services;

use App\Repositories\DevicesRepository;
use App\Repositories\ExperimentRepository;
use App\Repositories\MessagesRepository;
use App\Repositories\QueuesRepository;
use App\Repositories\ThreadRepository;
use App\Repositories\ThreadRunRepository;
use App\Repositories\UsersRepository;
use Nextras\Orm\Model\Model;

/**
 * Class OrmService
 * @package App\Services
 * @property-read UsersRepository $users
 * @property-read QueuesRepository $queues
 * @property-read MessagesRepository $messages
 * @property-read DevicesRepository $devices
 * @property-read ThreadRepository $threads
 * @property-read ThreadRunRepository $threadRuns
 * @property-read ExperimentRepository $experiments
 */
class OrmService extends Model {

}
