<?php

namespace App\Presenters;

use App\DataGrids\CommonDataGrid;
use App\Interfaces\AdminPresenterInterface;
use App\Interfaces\AuthorizedPresenterInterface;
use App\Model\CommonModel;
use App\Model\Portal;
use App\Model\User;
use App\Repositories\CommonRepository;
use App\Services\ImageService;
use App\Services\OrmService;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Presenter;
use Nette\Security\Identity;

class BasePresenter extends Presenter {
    /** @var  CommonModel|mixed */
    protected $item;

    public function startup() {
        parent::startup();

        if ($this instanceof AuthorizedPresenterInterface && !$this->user->isLoggedIn()) {
            $this->redirect(':Front:Sign:in', ['backlink' => $this->storeRequest()]);
            $this->terminate();
        }
        $adminRoles = [
            User::ROLE_ADMIN,
            User::ROLE_ROOT
        ];
        if ($this instanceof AdminPresenterInterface && !in_array($this->user->identity->getRoles()[0], $adminRoles)) {
            throw new ForbiddenRequestException('Nothing to do here cunt ;)');
        }

        if ($this->user->isLoggedIn()) {
            $this->loggedInUser = $this->ormService->users->getById($this->user->id);
        }
    }

    /**
     * @param $dataGridName
     * @param $name
     * @param array $filters
     * @param Identity $editor
     * @return CommonDataGrid|mixed
     */
    protected function createDataGrid($dataGridName, $name, $filters = [], Identity $editor = null) {
        $className = '\App\DataGrids\\' . ucfirst($dataGridName) . 'DataGrid';
        $grid = new $className($this, $name, $this->ormService, $filters, $editor);
        return $grid;
    }

    /** @var  OrmService @inject */
    public $ormService;

    /** @var  User|null */
    protected $loggedInUser;
}
