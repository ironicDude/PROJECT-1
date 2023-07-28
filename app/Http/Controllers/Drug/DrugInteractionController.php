<?php

namespace App\Http\Controllers\Drug;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\ProductOverviewCollection;
use App\Http\Resources\ProductOverviewResource;
use Illuminate\Http\Request;
use App\Models\Drug;
use App\Models\InteractingDrug;
use App\Models\Interaction;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DrugInteractionController extends Controller
{
    use CustomResponse;

    /**
     * Check for interactions between two drugs based on their IDs.
     *
     * This method receives drug IDs from the client and checks for any interactions between the two drugs
     * using the Drug and InteractingDrug models. It retrieves the interaction description if an interaction
     * is found and returns it in a custom response format. If no interaction is found, an appropriate
     * error message is returned.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing drug IDs.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the interaction description or an error message.
     */
    public function checkInteraction(Request $request)
    {
        // Retrieve drug IDs from the request.
        $id = $request->id;
        $interactingId = $request->interactingId;

        // Find the Drug model corresponding to the given ID.
        $drug = Drug::findOrFail($id);

        // Check for interactions between the two drugs using the interactingDrugs() relationship.
        $interaction = $drug->interactingDrugs()->wherePivot('interacting_drug_id', $interactingId)->get();

        // Extract the interaction description from the result and respond accordingly with a custom response.
        $description = $interaction->pluck('pivot.description');
        if (count($description) == 0) {
            return self::customResponse('No interaction found', null, 404);
        } else {
            return self::customResponse('Interaction found', $description, 200);
        }
    }

    /**
     * Retrieve product information related to two drugs based on their models.
     *
     * This method receives two Drug models and retrieves product information related to those drugs.
     * It limits the results to a maximum of three available products per drug and returns the product overview
     * information in a custom response format. If no products are found, an appropriate error message
     * is returned.
     *
     * @param \App\Models\Drug $firstDrug The first Drug model instance.
     * @param \App\Models\Drug $secondDrug The second Drug model instance.
     * @return \Illuminate\Http\JsonResponse The JSON response containing product overview information or an error message.
     */
    public function index(Drug $firstDrug, Drug $secondDrug)
    {
        $firstDrugProducts = collect();
        $firstDrug->products->each(function ($product) use ($firstDrugProducts) {
            if ($product->isPurchased() && $product->purchasedProduct && $product->purchasedProduct->isAvailable()) {
                $firstDrugProducts->push($product);
            }
            if ($firstDrugProducts->count() == 3) {
                return false;
            }
        });

        $secondDrugProducts = collect();
        $secondDrug->products->each(function ($product) use ($secondDrugProducts) {
            if ($product->isPurchased() && $product->purchasedProduct && $product->purchasedProduct->isAvailable()) {
                $secondDrugProducts->push($product);
            }
            if ($secondDrugProducts->count() == 3) {
                return false;
            }
        });



        // Merge the product information of both drugs and retrieve the results.
        $products = $firstDrugProducts->union($secondDrugProducts);

        // Check if any products were found and respond accordingly with a custom response.
        if ($products->isEmpty()) {
            return self::customResponse('No products to retrieve', null, 404);
        } else {
            // Return the product overview information using the ProductOverviewCollection resource.
            return self::customResponse('Products retrieved', new ProductOverviewCollection($products), 200);
        }
    }
}
