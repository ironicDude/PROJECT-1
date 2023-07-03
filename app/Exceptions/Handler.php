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
            return $this->customResponse("Account is currently deactivated", null, 403);
        });
    } // end of register

    //modify the ModelNotFoundException message
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return $this->customResponse("Unable to locate the {$this->prettyModelNotFound($e)} you requested.", null, 404);
        }
        if ($e instanceof AuthorizationException){
            return $this->customResponse("Page does not exist", null, 404);
        }
        return parent::render($request, $e);
    } // end of render

    //helper function to beautify the ModelNotFoundException message
    private function prettyModelNotFound(ModelNotFoundException $exception): string
    {
        if (! is_null($exception->getModel())) {
            return Str::lower(ltrim(preg_replace('/[A-Z]/', ' $0', class_basename($exception->getModel()))));
        }

        return 'resource';
    } // end of prettyModelNotFound

}
