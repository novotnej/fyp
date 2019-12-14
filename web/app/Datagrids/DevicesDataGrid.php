<?php
namespace App\DataGrids;

use App\Model\Device;

/**
 * Class DevicesDatagrid
 * @package App\DataGrids
 */
class DevicesDataGrid extends CommonDataGrid {
    protected function init() {
        parent::init();
        $this->setDataSource($this->orm->devices->findAll());
        $this->addColumnNumber('id', 'ID')->setSortable();
        $this->addColumnText('name', 'Name')->setSortable()->setFilterText();

        //$this->addAction('edit', 'Edit', 'Device:edit')->setIcon('edit');
        $this->setDefaultPerPage(50);
    }
}