<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasMany;

/**
 * Class Queue
 * @package App\Model
 * @property string $name
 * @property ManyHasMany|Message[] $messages {m:m Message::$queues, isMain=true}
 * @property ManyHasMany|Device[] $devices {m:m Device::$queues, isMain=true}
 */
class Queue extends CommonModel {

}
