<?php

namespace App\Livewire;

use LivewireUI\Modal\ModalComponent;
use App\Models\Product;

class ViewProduct extends ModalComponent
{

    public Product $product;

    // public function mount()
    // {
    //     Gate::authorize('update', $this->product);
    // }

    public function render()
    {
        return view('livewire.view-product', ['product' => $this->product]);
    }

}
