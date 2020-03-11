<?php
namespace App\Repositories;

use Nextras\Orm\Entity\Reflection\PropertyMetadata;
use Nextras\Orm\Mapper\Dbal\DbalMapper;

class QueuesMapper extends CommonMapper {
    protected $tableName = 'queue';

    public function getManyHasManyParameters(PropertyMetadata $sourceProperty, DbalMapper $targetMapper) {
        if ($targetMapper instanceof MessagesMapper) {
            return ['message_in_queue', ['queue_id', 'message_id']];
        }

        if ($targetMapper instanceof DevicesMapper) {
            return ['device_in_queue', ['queue_id', 'device_id']];
        }
        return parent::getManyHasManyParameters($sourceProperty, $targetMapper);
    }
}