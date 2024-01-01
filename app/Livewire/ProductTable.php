<?php

namespace App\Livewire;

use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use App\Models\Product;

class ProductTable extends DataTableComponent
{
    protected $model = Product::class;

    protected $listeners = ['productAdded' => '$refresh'];

    public function configure(): void
    {
        // Open products in a new window
        $this->setPrimaryKey('id')
            ->setTableRowUrl(function($row) {
                return '#';
            })->setTrAttributes(function($row, $index) {
                return [
                    'onClick' => "window.open('/view-product/{$row->id}', '_blank', \"popup=yes,width=800,height=750\")",
                ];
            });
        // Set default sorting status for created_at
        $this->setDefaultSort('created_at', 'desc');
        // Set empty message
        $this->setEmptyMessage('No products have been downloaded yet. Click the Start button to start downloading products');
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

}
