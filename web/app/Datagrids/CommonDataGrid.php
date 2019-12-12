<?php
namespace App\DataGrids;

use App\Model\CommonModel;
use App\Services\ImageService;
use App\Services\OrmService;
use Nette\Security\Identity;
use Nette\Utils\Html;
use Ublaboo\DataGrid\DataGrid;

/**
 * Class CommonDataGrid
 * @package App\DataGrids
 */
abstract class CommonDataGrid extends DataGrid {
    /** @var  OrmService */
    protected $orm;
    /** @var array  */
    protected $conditions = [];
    /** @var  Identity */
    protected $editor;


    const ACTION_EDIT = 'edit';
    const ACTION_DETAIL = 'detail';
    const ACTION_DELETE = 'delete';

    /**
     * CommonDataGrid constructor.
     * @param null $parent
     * @param null $name
     * @param OrmService $ormService
     * @param array $filter
     * @param Identity|null $editor
     */
    public function __construct($parent = NULL, $name = NULL,  OrmService $ormService, $filter = [], Identity $editor = null) {
        parent::__construct($parent, $name);
        $this->orm = $ormService;
        $this->conditions = $filter;
        $this->editor = $editor;
        $this->setStrictSessionFilterValues(false);
        $this->init();
    }

    protected function init() {

    }

    protected function addYesNo($key, $label, $column = null, $callback = null) {
        if ($callback) {
            $this->addColumnStatus($key, $label, $column)
                ->addOption(true, 'YES')->setClass('btn-success')->endOption()
                ->addOption(false, 'NO')->setClass('btn-danger')->endOption()
            ->onChange[] = $callback;
        } else {
            $this->addColumnText($key, $label, $column)
                ->setRenderer(function(CommonModel $item) use ($key, $column) {
                   $label = Html::el('span');
                   $value = $column ? $item->$column : $item->$key;
                   if ($value) {
                       $label->setAttribute('class', 'badge badge-success')
                       ->setText(\L::yes());
                   } else {
                       $label->setAttribute('class', 'badge badge-danger')
                       ->setText(\L::no());
                   }
                   return $label;
                });
        }
    }

    /**
     * @param $name
     * @return bool
     */
    protected function hasColumn($name) {
        if (!isset($this->conditions['columns'])) {
            return true;
        }
        return in_array($name, $this->conditions['columns']);
    }

    /**
     * @param $name
     * @return bool
     */
    protected function hasAction($name) {
        if (!isset($this->conditions['actions'])) {
            return true;
        }
        return in_array($name, $this->conditions['actions']);
    }
}