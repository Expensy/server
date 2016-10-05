<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
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
   * @param  \Exception $exception
   *
   * @return void
   */
  public function report(Exception $exception) {
    parent::report($exception);
  }

  /**
   * Render an exception into an HTTP response.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Exception               $exception
   *
   * @return \Illuminate\Http\Response
   */
  public function render($request, Exception $exception) {
    return parent::render($request, $exception);
  }

  /**
   * Convert an authentication exception into an unauthenticated response.
   *
   * @param  \Illuminate\Http\Request $request
   * @param Exception                 $e
   *
   * @return \Illuminate\Http\Response
   * @internal param AuthenticationException $exception
   *
   */
  protected function unauthenticated($request, Exception $e) {
    if ($e instanceof TokenExpiredException) {
      return response()->json(['message' => 'Token expired'], $e->getStatusCode());
    } else if ($e instanceof TokenInvalidException) {
      return response()->json(['message' => 'Token invalid'], $e->getStatusCode());
    } else if ($e instanceof JWTException) {
      return response()->json(['message' => 'Token absent'], $e->getStatusCode());
    }

    return parent::render($request, $e);
  }
}
