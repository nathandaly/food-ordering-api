<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * @param Request $request
     * @param Exception $exception
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function render($request, Exception $exception)
    {
        $output = null;

        try {
            throw $exception;
        } catch (ModelNotFoundException | NotFoundHttpException $e) {
            $message = $exception->getMessage();

            if ($e instanceof NotFoundHttpException) {
                $message = 'Route not found.';
            }

            $output = [
                'message' => [
                    'error' => [
                        'message' => $message,
                        'error_code' => 0,
                        'status_code' => 404,
                    ],
                ],
                'status' => 404,
            ];
        } catch (QueryException $e) {
            $output = [
                'message' => [
                    'error' => [
                        'message' => 'A query exception has occurred.',
                        'error_code' => 0,
                        'status_code' => 500,
                    ],
                ],
                'status' => 500,
            ];
        }

        if ($output) {
            return response()->json($output['message'], $output['status']);
        }

        return parent::render($request, $exception);
    }
}
