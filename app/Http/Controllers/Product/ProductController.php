<?php

namespace App\Http\Controllers\Product;

use App\Exceptions\NameNotFoundException;
use App\Exceptions\SuggestionException;
use App\Http\Controllers\Controller;
use App\Http\Resources\CustomResponse;
use App\Http\Resources\Product\ProductFullResource;
use App\Http\Resources\Product\ProductOverviewCollection;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Models\DatedProduct;
use App\Models\Drug;
use App\Models\Pharmacy;
use App\Models\PurchasedProduct;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     * @return \Illuminate\Http\JsonResponse|\App\Http\Resources\Product\ProductOverviewCollection
     *         The JSON response containing search results or a suggestion, or a custom collection of products.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'minPrice' => 'min:0',
            'maxPrice' => 'min:0',
            'rating' => 'between:0,5|numeric',
            'dosageForm' => 'string',
            'route' => 'string',
            'category' => 'string',
            'labeller' => 'string',
        ]);

        if ($validator->fails()) {
            return self::customResponse('errors', $validator->errors(), 422);
        }
        try {
            // Retrieve a list of products based on the provided search parameters.
            $products = Product::index($request);
        } catch (NameNotFoundException $e) {
            return self::customResponse($e->getMessage(), null, 404);
        } catch (SuggestionException $e) {
            $data = [
                'product name' => $e->suggestedName,
                'product id' => $e->suggestedNameId
            ];
            return self::customResponse($e->getMessage(), $data, 404);
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
        $products = Product::searchNames($request->string);

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
        $labellers = Product::searchLabellers($request->string);

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
        $routes = Product::searchRoutes($request->string);

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
        $categories = Drug::searchCategories($request->string);

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
        $dosageForms = Product::searchDosageForms($request->string);

        // Check if any matching dosage forms were found and respond accordingly with a custom response.
        if (count($dosageForms) == 0) {
            return self::customResponse('No matches to return', null, 404);
        } else {
            return self::customResponse('Matches returned', $dosageForms, 200);
        }
    }

    ////////////////////////////////// EISSAWI //////////////////////////////////////////////////////////////////////////
    public function allProductsWithPrices()
    {
        return response()->json([
            Product::query()->join('prices', function ($join) {
                $join->on('prices.drug_id', '=', 'products.drug_id')
                    ->where('prices.id', '=', DB::raw('(select min(id) from prices where drug_id = products.drug_id)'));
            })
            ->select('products.*', 'prices.cost','prices.currency','prices.unit', DB::raw('prices.cost * 1.3 AS price'))
            ->paginate(15)
        ]);
    }

    public function withPrices(Request $request)
    {
        $request->validate([
            'products' => ['array', 'present'],
            'products.*' => ['required', 'exists:products,id'],
        ]);
        try {

            $productIds = $request['products'];
            $products=Product::query()->whereIn('products.id', $productIds)
                ->join('prices', function ($join) {
                    $join->on('prices.drug_id', '=', 'products.drug_id')
                         ->where('prices.id', '=', DB::raw('(select min(id) from prices where drug_id = products.drug_id)'));
                })
                ->select('products.*', 'prices.cost','prices.currency','prices.unit', DB::raw('prices.cost * 1.3 AS price'))
                ->get(15);

            // $products contain a collection of products along with their prices
            return self::customResponse("Product with Prices", $products);
        } catch (\Throwable $e) {
            return $e;
        }
    }


    public function getPurchase($id)
    {
        $data['purchase'] = Purchase::query()->find($id);

        $data['dated_products'] = DatedProduct::query()->where('purchase_id', '=', $id)->get();
        return $data;
    }


    public function purchase(Request $request)
    {
        $request->validate([
            'products' => ['array', 'present'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'min:1'],
            'products.*.unit' => ['required'],
            'employee_id' => ['required', 'exists:users,id'],
            'pharmacy_id' => ['required', 'exists:pharmacies,id']
        ]);

        try {
            // Get an array of product IDs from the request
            $products = $request['products'];
            $quantity = 0;
            foreach ($products as $product)
                $quantity += $product['quantity'];

            // Fetch the products and their corresponding prices based on the requested product IDs
            $productIds = array_column($products, 'id');
            $productsData = Product::query()->whereIn('products.id', $productIds)
                ->join('prices', function ($join) {
                    $join->on('prices.drug_id', '=', 'products.drug_id')
                         ->where('prices.id', '=', DB::raw('(select min(id) from prices where drug_id = products.drug_id)'));
                })
                ->select('products.*', DB::raw('prices.cost * 1.3 AS price'), 'prices.unit')
                ->get();
            $purchase_id = Purchase::query()->create([
                'employee_id' => $request['employee_id'],
                'total' => 0.0,
                'quantity' => 0
            ])['id'];
            $total = 0.0;
            $purchased_products = [];
            $dated_products = [];
            // Create 'PurchasedProduct' records for each product
            foreach ($productsData as $productData) {
                // Find the product in the $products array based on ID and unit
                $selectedProduct = collect($products)->firstWhere('id', $productData->id);

                // If a product with the same ID and unit is found in the request, create the 'PurchasedProduct' record

                if ($selectedProduct && $selectedProduct['unit'] === $productData->unit) {
                    if (PurchasedProduct::query()->find($productData->id) == null) {
                        $purchased_products[] = PurchasedProduct::query()->create([
                            'id' => $productData->id,
                            'price' => $productData->price,
                            'order_limit' => 5,
                            'minimum_stock_level' => 1,
                        ]);
                    }
                    $total += ($productData->price / 1.3) * $selectedProduct['quantity'];

                    $dated_products[] = DatedProduct::query()->create([
                        'purchase_id' => $purchase_id,
                        'product_id' => $productData->id,
                        'quantity' => $selectedProduct['quantity'],
                        'purchase_price' => $productData->price / 1.3,
                        'expiry_date' => Carbon::now()->addYears(3),
                        'manufacturing_date' => Carbon::now(),
                    ]);
                }
            }
            // update purchase total
            Purchase::query()->find($purchase_id)->update([
                'total' => $total,
                'quantity' => $quantity
            ]);

            $pharmacy = Pharmacy::query()->find($request['pharmacy_id']);
            if($total>$pharmacy->money)
                return self::customResponse("Please charge your pharmacy money.");
            $pharmacy->money = $pharmacy->money - $total;
            $pharmacy->save();
            $data['purchased_products'] = $purchased_products;
            $data['purchase'] = Purchase::query()->find($purchase_id);
            $data['dated_products'] = $dated_products;
            return self::customResponse("Purchased Products created successfully.", $data);
        } catch (\Throwable $e) {
            return $e;
        }
    }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}
