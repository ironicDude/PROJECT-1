<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use App\Exceptions\AccountDeactivatedException;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Resources\CustomResponse;

class Handler extends ExceptionHandler
{
    use CustomResponse;
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // $this->reportable(function (AccountDeactivatedException $e, $request){
        //     //
        // });
        // Register a renderable callback for AccountDeactivatedException
        $this->renderable(function (AccountDeactivatedException $e, $request) {
            return self::customResponse("Account is currently deactivated", null, 403);
        });

        $this->renderable(function (QuantityExceededOrderLimitException $e, $request) {
            return self::customResponse("For some regulatory purposes, you cannot order as many of this product", null, 422);
        });

        $this->renderable(function (OutOfStockException $e, $request) {
            return self::customResponse("Out of stock", null, 422);
        });

        $this->renderable(function (EmptyCartException $e, $request) {
            return self::customResponse("The cart is empty", null, 422);
        });

        $this->renderable(function (ItemNotInCartException $e, $request) {
            return self::customResponse("The item is not in the cart", null, 422);
        });

        $this->renderable(function (NullQuantityException $e, $request) {
            return self::customResponse("You should pass a quantity", null, 422);
        });

        $this->renderable(function (NotEnoughMoneyException $e, $request) {
            return self::customResponse("You don't have enough money", null, 422);
        });

        $this->renderable(function (PrescriptionRequiredException $e, $request) {
            return self::customResponse("Prescription required", null, 422);
        });

        $this->renderable(function (NullAddressException $e, $request) {
            return self::customResponse("Address required", null, 422);
        });

        $this->renderable(function (SameQuantityException $e, $request) {
            return self::customResponse("Same quantity as the current quantity", null, 422);
        });

        // $this->renderable(function (InShortageException $e, $request) {
        //     return self::customResponse("In shortage", null, 422);
        // });

        $this->renderable(function (ItemAlreadyInCartException $e, $request) {
            return self::customResponse("Product already in cart. You can modify its quantity.", null, 422);
        });
    } // end of register

    //modify the ModelNotFoundException message
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return CustomResponse::customResponse("Unable to locate the {$this->prettyModelNotFound($e)} you requested.", null, 404);
        }
        if ($e instanceof AuthorizationException) {
            return CustomResponse::customResponse("Page does not exist", null, 404);
        }
        return parent::render($request, $e);
    } // end of render

    //helper function to beautify the ModelNotFoundException message
    private function prettyModelNotFound(ModelNotFoundException $exception): string
    {
        if (!is_null($exception->getModel())) {
            return Str::lower(ltrim(preg_replace('/[A-Z]/', ' $0', class_basename($exception->getModel()))));
        }

        return 'resource';
    } // end of prettyModelNotFound

}
