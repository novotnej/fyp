<?php
namespace App\DataGrids;

use App\Model\Queue;

/**
 * Class DevicesDatagrid
 * @package App\DataGrids
 */
class QueuesDataGrid extends CommonDataGrid {
    protected function init() {
        parent::init();
        $this->setDataSource($this->orm->queues->findAll());
        $this->addColumnNumber('id', 'ID')->setSortable();
        $this->addColumnText('name', 'Name')->setSortable()->setFilterText();

        $this->addAction('edit', 'Modify', 'Queue:edit')->setIcon('edit');
        $this->setDefaultPerPage(50);
    }
}