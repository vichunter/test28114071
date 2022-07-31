<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $forceJson = false;

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $handler =& $this;

        $this->renderable(function (ModelNotFoundException $e, $request) {
            return new JsonResponse([
                'errors' => ['Model not found: ' . $e->getModel()]
            ], 404);
        });

        $this->renderable(function (ValidationException $e, $request) use ($handler) {
            $handler->forceJson = true;
        });
    }

    protected function prepareException(Throwable $e)
    {
        if ($e instanceof ModelNotFoundException) {
            return $e;
        }

        return parent::prepareException($e);
    }

    protected function shouldReturnJson($request, Throwable $e)
    {
        return $this->forceJson || parent::shouldReturnJson($request, $e);
    }
}
