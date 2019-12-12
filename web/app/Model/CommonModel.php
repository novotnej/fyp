<?php
namespace App\Model;

use Nextras\Orm\InvalidArgumentException;
use \Nextras\Orm\Entity\Entity;

/**
 * Class CommonModel
 * @package App\Model
 * @property-read int $id {primary}
 */
class CommonModel extends Entity {

    /**
     * CommonModel constructor.
     * @param array $data
     */
    public function __construct($data = []) {
        parent::__construct();
        $this->updateValues($data);
    }

    /**
     * @param array $data
     * @return CommonModel
     */
    public function updateValues($data = []) {
        foreach ($data as $key => $value) {
            try {
                $this->{$key} = $value;
            } catch (InvalidArgumentException $e) {
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        if (isset($this->name)) {
            return $this->name;
        }
        if (isset($this->title)) {
            return $this->title;
        }
        return "";
    }
}