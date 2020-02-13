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

        $this->addAction('edit', 'Modify', 'Device:edit')->setIcon('edit');
        $this->addAction('delete', 'Delete', 'Device:delete')->setIcon('trash');
        $this->addAction('detail', 'Detail', 'Device:detail')->setIcon('detail');
        $this->setDefaultPerPage(50);
    }
}