<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
class Handler extends ExceptionHandler
{
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

        $this->reportable(function (AccountDeactivatedException $e, $request){
            //
        });
        // Register a renderable callback for AccountDeactivatedException
        $this->renderable(function (AccountDeactivatedException $e, $request) {
            return response()->json([
                'message' => 'Your account is currently deactivated'
            ], 403);
        });
    } // end of register

    //modify the ModelNotFoundException message
    public function render($request, Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return new JsonResponse([
                'message' => "Unable to locate the {$this->prettyModelNotFound($e)} you requested."
            ], 404);
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
