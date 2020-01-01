<?php
namespace App\Repositories;

use App\Model\Message;

class MessagesRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [Message::class];
    }
}