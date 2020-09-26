<?php

use Dibi\DataSource;
use Nette\Utils\Paginator;

abstract class BaseListComponent extends BaseComponent {

    private int $limit = 10;

    /** @persistent */
    public $orderBy;

    /** @persistent */
    public $sorting;

    private DataSource $source;

    public function getLimit(): int {
        return $this->limit;
    }

    /**
     * It sets default sorting of the data source.
     *
     * @param string $column
     * @param string $sorting
     */
    public function setDefaultSorting($column, $sorting = 'ASC'): void {
        if (empty($this->orderBy)) {
            $this->sort($column, $sorting);
        }
    }

    public function setLimit(int $limit): void {
        $this->limit = $limit;
    }

    public function setSource(DataSource $source): void {
        $this->source = $source;
    }

    // ---- PROTECTED METHODS
    protected function beforeRender(): void {
        parent::beforeRender();
        if (!empty($this->orderBy)) {
            $this->getSource()->orderBy($this->orderBy, $this->sorting);
        }
    }

    protected function createComponentPaginator(): VisualPaginatorComponent {
        $paginator = new VisualPaginatorComponent($this->getContext());
        $paginator->paginator->itemsPerPage = $this->getLimit();
        $paginator->paginator->itemCount = $this->getSource()->count();
        return $paginator;
    }

    protected function getPaginator(): Paginator {
        return $this->getComponent("paginator")->getPaginator();
    }

    protected function getSource(): DataSource {
        return $this->source;
    }

    /**
     * This method sorts a source of the list.
     *
     * @param string $column The column which is used for sorting.
     * @param string $sorting Direction of sorting (ASC/DESC). If it is NULL,
     *                the direction which is oposite to previous direction
     *                is used.
     */
    protected function sort($column, $sorting = null): void {
        $this->orderBy = $column;
        if (empty($sorting)) {
            $this->sorting = ($this->sorting == 'ASC') ? 'DESC' : 'ASC';
        } else {
            $this->sorting = $sorting;
        }
    }
}
