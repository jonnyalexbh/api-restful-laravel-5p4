<?php

namespace App\Exceptions;

use Exception;
use App\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
  use ApiResponser;

  /**
  * A list of the exception types that should not be reported.
  *
  * @var array
  */
  protected $dontReport = [
    \Illuminate\Auth\AuthenticationException::class,
    \Illuminate\Auth\Access\AuthorizationException::class,
    \Symfony\Component\HttpKernel\Exception\HttpException::class,
    \Illuminate\Database\Eloquent\ModelNotFoundException::class,
    \Illuminate\Session\TokenMismatchException::class,
    \Illuminate\Validation\ValidationException::class,
  ];

  /**
  * Report or log an exception.
  *
  * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
  *
  * @param  \Exception  $exception
  * @return void
  */
  public function report(Exception $exception)
  {
    parent::report($exception);
  }

  /**
  * Render an exception into an HTTP response.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Exception  $exception
  * @return \Illuminate\Http\Response
  */
  public function render($request, Exception $exception)
  {
    if ($exception instanceof ValidationException) {
      return $this->convertValidationExceptionToResponse($exception, $request);
    }

    if ($exception instanceof ModelNotFoundException) {
      $modelo = strtolower(class_basename($exception->getModel()));
      return $this->errorResponse("No existe ninguna instancia de {$modelo} con el id especificado", 404);
    }

    if ($exception instanceof AuthenticationException) {
      return $this->unauthenticated($request, $e);
    }
    
    if ($exception instanceof AuthorizationException) {
      return $this->errorResponse('No posee permisos para ejecutar esta acción', 403);
    }

    if ($exception instanceof NotFoundHttpException) {
      return $this->errorResponse('No se encontró la URL especificada', 404);
    }

    return parent::render($request, $exception);
  }

  /**
  * Convert an authentication exception into an unauthenticated response.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  \Illuminate\Auth\AuthenticationException  $exception
  * @return \Illuminate\Http\Response
  */
  protected function unauthenticated($request, AuthenticationException $exception)
  {
    return $this->errorResponse('No autenticado.', 401);
  }

  /**
  * Create a response object from the given validation exception.
  *
  * @param  \Illuminate\Validation\ValidationException  $e
  * @param  \Illuminate\Http\Request  $request
  * @return \Symfony\Component\HttpFoundation\Response
  */
  protected function convertValidationExceptionToResponse(ValidationException $e, $request)
  {
    $errors = $e->validator->errors()->getMessages();
    return $this->errorResponse($errors, 422);
  }

}
