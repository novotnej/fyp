<?php
namespace App\Repositories;

use App\Model\CommonModel;
use Nextras\Orm\Repository\Repository;

abstract class CommonRepository extends Repository {

    /**
     * @param $email
     * @return CommonModel|NULL
     */
    public function getByEmail($email) {
        return $this->getBy(['email' => $email]);
    }

    /**
     * @param int $n
     * @return \Nextras\Orm\Collection\ICollection|CommonModel[]
     */
    public function findRandom($n = 1) {
        return $this->findAll()
            ->orderBy('RAND()')
            ->limitBy($n);
    }

    public function count() {
        return $this->findAll()->countStored();
    }
}
