<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CustomResponse;
use App\Models\Product;
use App\Models\User;

class AllergyController extends Controller
{
    use CustomResponse;

    /**
     * Toggle the allergy status of a product for the authenticated user.
     *
     * This method toggles the allergy status of the provided product for the authenticated user.
     * If the user was already allergic to the product, it removes the allergy; otherwise, it adds the allergy.
     *
     * @param \App\Models\Product $product The product for which to toggle the allergy status.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the success of the operation.
     */
    public function toggleAllergy(Product $product)
    {
        $user = Auth::user();

        // Toggle the allergy status of the product for the authenticated user.
        $user->allergies()->toggle($product);

        return self::customResponse('Allergy toggled', null, 200);
    }

    /**
     * Check the allergy status of a product for the authenticated user.
     *
     * This method checks the allergy status of the provided product for the authenticated user.
     * It returns a response indicating whether the user is allergic, indirectly allergic, or not allergic to the product.
     *
     * @param \App\Models\Product $product The product for which to check the allergy status.
     * @return \Illuminate\Http\JsonResponse A JSON response with the allergy status information.
     */
    public function checkAllergy(Product $product)
    {
        $user = Auth::user();

        // Check if the user is allergic to the product or indirectly allergic to it.
        if ($user->isAllergicTo($product)) {
            return self::customResponse('RED: User is allergic to this product', true, 200);
        } elseif ($user->isIndirectlyAllergicTo($product)) {
            return self::customResponse('YELLO: User might be allergic to this product', true, 200);
        }

        // User is not allergic to the product.
        return self::customResponse('GREEN: User is not allergic to this product', false, 200);
    }

    /**
     * Retrieve a list of all the user's allergies.
     *
     * This method retrieves a list of all the allergies for the authenticated user.
     * The allergies are returned with additional information, such as the associated drug name.
     *
     * @return \Illuminate\Http\JsonResponse A JSON response containing the user's allergies.
     */
    public function index()
    {
        $user = Auth::user();

        // Load the allergies for the user with the associated drug name.
        $user->load('allergies:id,drug_id,name');

        // Map the allergies to a formatted array containing the allergy ID, drug ID, and name.
        $allergies = $user->allergies->map(function ($allergy) {
            return ['id' => $allergy->id, 'drug_id' => $allergy->drug_id, 'name' => $allergy->name . ' [' . $allergy->drug->name . ']'];
        });

        // Check if the user has any allergies and return the appropriate response.
        if ($allergies->isEmpty()) {
            return self::customResponse('no allergies returned', null, 404);
        }

        return self::customResponse('allergies returned', $allergies, 200);
    }
}
