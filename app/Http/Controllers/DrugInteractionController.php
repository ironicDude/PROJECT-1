<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponse;
use App\Http\Resources\ProductOverviewCollection;
use App\Http\Resources\ProductOverviewResource;
use Illuminate\Http\Request;
use App\Models\Drug;
use App\Models\InteractingDrug;
use App\Models\Interaction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class DrugInteractionController extends Controller
{
    use CustomResponse;

    public function search(Request $request)
    {
        $string = $request->string;
        $drug = Drug::search($string, 1);
        $products = Product::search($string, 2);

        $data = [$drug, $products];
        if(count($data) == 0){
            return $this->customResponse('No matches', null, 404);
        }
        else{
            return $this->customResponse('matches returned', $data, 200);
        }
    }

    public function checkInteraction(Request $request)
    {
        $id = $request->id;
        $interactingId = $request->interactingId;
        $drug = Drug::findOrFail($id);
        $interaction = $drug->interactingDrugs()->wherePivot('interacting_drug_id', $interactingId)->get();
        $description = $interaction->pluck('pivot.description');
        if(count($description) == 0){
            return $this->customResponse('no interaction found', null, 404);
        }
        else{
            return $this->customResponse('interaction found', $description, 200);
        }
    }

    public function index(Drug $firstDrug, Drug $secondDrug)
    {
        $firstDrugProducts = $firstDrug->products()->limit(3);
        $secondDrugProducts = $secondDrug->products()->limit(3);
        $products = $firstDrugProducts->union($secondDrugProducts)->get();
        if($products->isEmpty()){
            return $this->customResponse('no products to retrieve', null, 404);
        }
        else{
            return $this->customResponse('products retrieved', new ProductOverviewCollection($products), 200);
        }
    }
}


