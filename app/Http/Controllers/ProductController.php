<?php

namespace App\Http\Controllers;

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

    public function index(Request $request)
    {
        $products = Product::index($request);

        if (
            !empty($request->name) && empty($request->minPrice) &&
            empty($request->maxPrice) && empty($request->category) &&
            empty($request->labeller) && empty($request->route) &&
            empty($request->rating) && empty($request->dosageForm) &&
            empty($request->otc) && $products->isEmpty()
        ) {
            $name = $request->name;
            $response = Http::get("https://rxnav.nlm.nih.gov/REST/spellingsuggestions.json?name={$name}");

            if (empty(($response->json()['suggestionGroup']['suggestionList']))) {
                return response()->json(['message' => "Your search - {$name} - did not match any documents. Suggestions: Make sure all words are spelled correctly. Try different words.Try more general words. Try fewer words."], 404);
            }
            $suggestedName = $response->json()['suggestionGroup']['suggestionList']['suggestion'][0];
            return response()->json(['message' => "Did you mean {$suggestedName}?"], 404);
        }

        $collection = new ProductOverviewCollection($products);
        return $collection;
    } //end of index

    public function searchNames(Request $request)
    {

        $products = Product::searchNames($request);

        if (count($products) == 0) {
            return $this->customResponse('no matches to return', null, 404);
        } else {
            return $this->customResponse('matches returned', $products, 200);
        }
    } //end of barSearch

    public function show(Product $product)
    {
        return $this->customResponse('product returned', new ProductFullResource($product), 200);
    } //end of show

    public function searchLabellers(Request $request)
    {
        $labellers = Product::searchLabellers($request);
        if (count($labellers) == 0) {
            return $this->customResponse('no matches to return', null, 404);
        } else {
            return $this->customResponse('matches returned', $labellers, 200);
        }
    } //end of searchLabellers

    public function searchRoutes(Request $request)
    {
        $routes = Product::searchRoutes($request);
        if (count($routes) == 0) {
            return $this->customResponse('no matches to return', null, 404);
        } else {
            return $this->customResponse('matches returned', $routes, 200);
        }
    } //end of getRoutes

    public function searchCategories(Request $request)
    {
        $categories = Drug::searchCategories($request);
        if (count($categories) == 0) {
            return $this->customResponse('no matches to return', null, 404);
        } else {
            return $this->customResponse('matches returned', $categories, 200);
        }
    } //end of searchCategories

    public function searchDosageForms(Request $request)
    {
        $dosageForms = Product::searchDosageForms($request);
        if (count($dosageForms) == 0) {
            return $this->customResponse('no matches to return', null, 404);
        } else {
            return $this->customResponse('matches returned', $dosageForms, 200);
        }
    } //end of searchDosageForms
}
