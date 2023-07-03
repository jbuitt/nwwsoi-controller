<?php

namespace App\Http\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Product;

class ProductTable extends DataTableComponent
{
    protected $model = Product::class;

    protected $listeners = ['productAdded'];

    public function configure(): void
    {
        // Configure row clicks the Laravel Livewire Modal way
        // $this->setPrimaryKey('id')
        //     ->setTableRowUrl(function($row) {
        //         return '#';
        //     })
        //     ->setTrAttributes(function($row, $index) {
        //         return [
        //             'onClick' => "Livewire.emit('openModal', 'view-product', {\"product\":\"$row->id\"})",
        //         ];
        //     });
        // Configure row clicks the Tailwind Modal way
        // $this->setPrimaryKey('id')
        //     ->setTableRowUrl(function($row) {
        //         return '#';
        //     })
        //     ->setTrAttributes(function($row, $index) {
        //         return [
        //             'data-modal-target' => 'productModal',
        //             'data-modal-toggle' => 'productModal',
        //             'data-product-id' => $row->id,
        //             'onClick' => "Livewire.emit('loadProductContent', " . $row->id . ");",
        //         ];
        //     });
        // Configure row clicks the new window way
        $this->setPrimaryKey('id')
            ->setTableRowUrl(function($row) {
                return route('view-product', $row);
            })
            ->setTableRowUrlTarget(function($row) {
                return 'newwin';
            });
        // Set default sorting status for created_at
        $this->setDefaultSort('created_at', 'desc');
        // Set empty message
        $this->setEmptyMessage('No products found');
    }

    public function columns(): array
    {
        return [
            Column::make("Id", "id")
                ->sortable()
                ->hideIf(TRUE),
            Column::make("File Name", "name")
                ->sortable()
                ->searchable(),
            Column::make('Type')
                ->label(
                    fn($row, Column $column) => $this->getProductType($row, $column),
                ),
            Column::make('WFO')
                ->label(
                    fn($row, Column $column) => $this->getWfo($row, $column),
                ),
            Column::make("Created at", "created_at")
                ->sortable(),
            // Column::make("Updated at", "updated_at")
            //     ->sortable(),
        ];
    }

    // public function customView(): string
    // {
    //     return 'livewire.view-product';
    // }

    public function getProductType($row, $column)
    {
        return strtoupper(substr($row->name, 12, 3));
    }

    public function getWfo($row, $column)
    {
        return strtoupper(substr($row->name, 0, 4));
    }

    public function productAdded()
    {
        $this->emit('refreshDatatable');
    }

}
