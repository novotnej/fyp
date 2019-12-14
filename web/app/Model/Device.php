<?php
namespace App\Model;

use Nette\Security\Passwords;
use Nextras\Orm\Relationships\ManyHasMany;


/**
 * Class Device
 * @package App\Model
 * @property string $name
 * @property string $secret
 * @property ManyHasMany|Queue[] $queues {m:m Queue::$devices}
 */
class Device extends CommonModel {

    /**
     * @param string $secret
     */
    public function setNewSecret($secret) {
        $this->secret = Passwords::hash($secret);
    }

}
