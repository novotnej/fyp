<?php
namespace App\DataGrids;

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
        $this->addAction('delete', 'Delete', 'Queue:delete')->setIcon('trash');
        $this->addAction('detail', 'Detail', 'Queue:detail')->setIcon('detail');
        $this->setDefaultPerPage(50);
    }
}