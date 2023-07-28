<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\DrugResource;
use App\Http\Resources\ProductFullResource;
use App\Http\Resources\ProductOverviewCollection;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Drug;
use App\Models\DrugCategory;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ProductController extends Controller
{
    use CustomResponse;

    /**
     * Retrieve a list of products based on the provided search parameters.
     *
     * This method receives a HTTP request containing various search parameters and fetches
     * a list of products matching the specified criteria using the Product model's index method.
     * If the search does not yield any results and the request includes a product name, the method
     * attempts to suggest a corrected product name using an external API (rxnav.nlm.nih.gov).
     * If suggestions are found, it returns them as a response. Otherwise, it responds with a 404 error.
     * If there are matching products, it returns the results in a custom ProductOverviewCollection format.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing search parameters.
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\ProductOverviewCollection
     *         The JSON response containing search results or a suggestion, or a custom collection of products.
     */
    public function index(Request $request)
    {
        // Retrieve a list of products based on the provided search parameters.
        $products = Product::index($request);

        // Check if the search query did not yield any results and a product name was provided.
        if (
            !empty($request->name) && empty($request->minPrice) &&
            empty($request->maxPrice) && empty($request->category) &&
            empty($request->labeller) && empty($request->route) &&
            empty($request->rating) && empty($request->dosageForm) &&
            empty($request->otc) && empty($request->availability) && $products->isEmpty()
        ) {
            // Attempt to suggest a corrected product name using an external API (rxnav.nlm.nih.gov).
            $name = $request->name;
            $response = Http::get("https://rxnav.nlm.nih.gov/REST/spellingsuggestions.json?name={$name}");

            // If suggestions are found, return them as a response.
            if (empty(($response->json()['suggestionGroup']['suggestionList']))) {
                return response()->json(['message' => "Your search - {$name} - did not match any documents. Suggestions: Make sure all words are spelled correctly. Try different words.Try more general words. Try fewer words."], 404);
            }
            $suggestedName = $response->json()['suggestionGroup']['suggestionList']['suggestion'][0];
            return response()->json(['message' => "Did you mean {$suggestedName}?"], 404);
        }

        // If matching products are found, return them in a custom ProductOverviewCollection format.
        $collection = new ProductOverviewCollection($products);
        return $collection;
    }

    /**
     * Search for product names that match the provided search term.
     *
     * This method receives a HTTP request containing a search term and performs a search for product names
     * matching the specified term using the Product model's searchNames method. It returns the matching names
     * in a custom response format.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search term.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the matching product names or an error message.
     */
    public function searchNames(Request $request)
    {
        // Search for product names that match the provided search term using the Product model's searchNames method.
        $products = Product::searchNames($request);

        // Check if any matching product names were found and respond accordingly with a custom response.
        if (count($products) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $products, 200);
        }
    }

    /**
     * Show detailed information about a specific product.
     *
     * This method receives a specific Product model and returns detailed information
     * about the product in a custom ProductFullResource format using the ProductFullResource class.
     *
     * @param \App\Models\Product $product The Product model instance.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the detailed product information.
     */
    public function show(Product $product)
    {
        return self::customResponse('Product returned', new ProductFullResource($product), 200);
    }

    /**
     * Search for labellers based on the provided search criteria.
     *
     * This method receives a HTTP request containing search criteria and performs a search for
     * labellers (drug manufacturers) that match the specified criteria using the Product model's
     * searchLabellers method. It returns the matching labellers in a custom response format.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search criteria.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the matching labellers or an error message.
     */
    public function searchLabellers(Request $request)
    {
        // Search for labellers based on the provided search criteria using the Product model's searchLabellers method.
        $labellers = Product::searchLabellers($request);

        // Check if any matching labellers were found and respond accordingly with a custom response.
        if (count($labellers) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $labellers, 200);
        }
    }

    /**
     * Search for routes of administration based on the provided search criteria.
     *
     * This method receives a HTTP request containing search criteria and performs a search for
     * routes of administration that match the specified criteria using the Product model's
     * searchRoutes method. It returns the matching routes in a custom response format.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search criteria.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the matching routes or an error message.
     */
    public function searchRoutes(Request $request)
    {
        // Search for routes of administration based on the provided search criteria using the Product model's searchRoutes method.
        $routes = Product::searchRoutes($request);

        // Check if any matching routes were found and respond accordingly with a custom response.
        if (count($routes) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $routes, 200);
        }
    }

    /**
     * Search for drug categories based on the provided search criteria.
     *
     * This method receives a HTTP request containing search criteria and performs a search for
     * drug categories that match the specified criteria using the Drug model's searchCategories method.
     * It returns the matching categories in a custom response format.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search criteria.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the matching drug categories or an error message.
     */
    public function searchCategories(Request $request)
    {
        // Search for drug categories based on the provided search criteria using the Drug model's searchCategories method.
        $categories = Drug::searchCategories($request);

        // Check if any matching categories were found and respond accordingly with a custom response.
        if (count($categories) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $categories, 200);
        }
    }

    /**
     * Search for dosage forms based on the provided search criteria.
     *
     * This method receives a HTTP request containing search criteria and performs a search for
     * dosage forms that match the specified criteria using the Product model's searchDosageForms method.
     * It returns the matching dosage forms in a custom response format.
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request containing the search criteria.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the matching dosage forms or an error message.
     */
    public function searchDosageForms(Request $request)
    {
        // Search for dosage forms based on the provided search criteria using the Product model's searchDosageForms method.
        $dosageForms = Product::searchDosageForms($request);

        // Check if any matching dosage forms were found and respond accordingly with a custom response.
        if (count($dosageForms) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $dosageForms, 200);
        }
    }
}
