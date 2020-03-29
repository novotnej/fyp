<?php
namespace App\Model;

use Nextras\Orm\Relationships\ManyHasMany;
use Nextras\Orm\Relationships\ManyHasOne;

/**
 * Class Message
 * @package App\Model
 * @property string $content
 * @property ManyHasOne|User $user {m:1 User, oneSided=true}
 * @property \DateTimeImmutable $created {default now}
 * @property \DateTimeImmutable|null $expiration {default null}
 * @property  ManyHasMany|Queue[] $queues {m:m Queue::$messages}
 */
class Message extends CommonModel {
    
}
