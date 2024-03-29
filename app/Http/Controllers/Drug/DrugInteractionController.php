<?php

namespace App\Http\Controllers\Drug;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Product\ProductOverviewCollection;
use Illuminate\Http\Request;
use App\Models\Drug;
use App\Models\InteractingDrug;
use App\Models\Interaction;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'interactingId' => 'required|integer',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        $description = Drug::checkInteraction($request->id, $request->interactingId);
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
        $products = Drug::getRelatedInteractionProducts($firstDrug, $secondDrug);
        if ($products->isEmpty()){
            return self::customResponse('These drugs have no available products', null, 200);
        }
        return self::customResponse('Relateed products retrieved', new ProductOverviewCollection($products), 200);
    }


    /**
     * Search for drugs and products based on the provided search string.
     *
     * This method receives a search string from the client and performs a search for drugs and products
     * based on the provided string. It utilizes the Drug and Product models to perform the search.
     * The results are then returned in a custom response format, providing feedback on the search outcome.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search string.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the search results or an error message.
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'string' => 'required|string',
        ]);
        if($validator->fails()){
            return self::customResponse('errors', $validator->errors(), 422);
        }
        // Perform the drug and product search using the search method in the Drug and Product models.
        $drug = Drug::searchNames($request->string, 1);
        $products = Product::searchNames($request->string, 2);

        if (count($drug) == 0 && count($products) == 0) {
            return self::customResponse('No matches', null, 404);
        }
        $data = array_merge($drug, $products);
        return self::customResponse('Matches returned', $data, 200);
    }

}
