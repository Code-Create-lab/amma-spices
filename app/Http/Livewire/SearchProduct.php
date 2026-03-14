<?php

namespace App\Http\Livewire;

use App\Models\Product;
use Livewire\Component;

class SearchProduct extends Component
{

    public $searchText = "";
    public $products = [];

    public function searchProduct()
    {
        if ($this->searchText != "") {

            $this->products = Product::where('product_name', 'LIKE', "%{$this->searchText}%")
                ->where('is_deleted', 0)
                ->select('product_name', 'slug', 'product_image')
                ->take(5)
                ->get()->toArray();
        } else {


            $this->products = Product::where('is_deleted', 0)
                ->select('product_name', 'slug', 'product_image')
                ->take(5)
                ->get()->toArray();
        }
    }


    public function submit()
    {


        return redirect()->to('/search-results?q=' . urlencode($this->searchText));
        // dd($this->searchText);

    }

    public function render()
    {
        return view('livewire.search-product');
    }
}
