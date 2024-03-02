<?php

namespace App\Exceptions;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

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

        });
    }

    public function render($request, Throwable $e)
    {
        if($e instanceof ModelNotFoundException) return $this->handleModelNotFoundException($request, $e);
        if($e instanceof HttpException && $e->getStatusCode() === 403)
        {
            return $this->handleNoPermissiontoAcceptTheResource($e);
        }

        return parent::render($request,$e);
    }
    public function handleModelNotFoundException($request,ModelNotFoundException $e)
    {
        $model = class_basename($e->getModel());
        $baseService = app(BaseService::class);
        return $baseService->apiResponse([
            'error' => $model.' was not found with '. implode(',',$e->getIds())
        ],404);
    }

    public function handleNoPermissiontoAcceptTheResource(HttpException $e)
    {
        $baseService = app(BaseService::class);
        return $baseService->apiResponseWithAuthorizationFailedError([
            'error' => 'You donot have permission to access this resource'
        ],403);
    }
}
