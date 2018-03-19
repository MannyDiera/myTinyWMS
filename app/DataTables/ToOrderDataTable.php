<?php

namespace Mss\DataTables;

use Mss\Models\Article;

class ToOrderDataTable extends ArticleDataTable
{
    /**
     * @var bool
     */
    protected $sortingEnabled = false;

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $this->rawColumns[] = 'checkbox';
        $dataTales = parent::dataTable($query);
        $dataTales->setRowClass(function ($article) {
            return '';
        })
        ->addColumn('checkbox', 'dashboard.to_order_list_checkbox');

        return $dataTales;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \Mss\Models\Article $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Article $model)
    {
        return $model->newQuery()
            ->withCurrentSupplierArticle()->withCurrentSupplier()
            ->with(['category', 'suppliers', 'unit', 'tags'])
            ->whereRaw('quantity <= min_quantity');
    }

    protected function getColumns()
    {
        $columns = parent::getColumns();
        $checkboxCol = ['data' => 'checkbox', 'name' => 'checkbox', 'title' => 'Neue Bestellung', 'width' => '10px', 'orderable' => false];
        $columns = array_prepend($columns, $checkboxCol);
        return $columns;
    }
}