<?php

namespace App\Http\Controllers\Drug;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductOverviewCollection;
use App\Models\Drug;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Resources\CustomResponse;
class DrugController extends Controller
{
    use CustomResponse;
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
        // Retrieve the search string from the request.
        $string = $request->string;

        // Perform the drug and product search using the search method in the Drug and Product models.
        $drug = Drug::search($string, 1);
        $products = Product::search($string, 2);

        // Combine the search results for drugs and products into an array.
        $data = [$drug, $products];

        // Check if any search results were found and respond accordingly with a custom response.
        if (count($data) == 0) {
            return self::customResponse('No matches', null, 404);
        } else {
            return self::customResponse('Matches returned', $data, 200);
        }
    }
}
