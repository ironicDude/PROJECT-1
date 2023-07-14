<?php

namespace App\Http\Controllers;

use App\Http\Resources\CustomResponse;
use Illuminate\Http\Request;
use App\Models\Drug;
use App\Models\InteractingDrug;
use App\Models\Interaction;
use App\Models\Product;

class DrugInteractionController extends Controller
{
    use CustomResponse;

    public function search(string $string)
    {
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

    public function checkInteraction(int $id, int $interactingId)
    {
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
}

