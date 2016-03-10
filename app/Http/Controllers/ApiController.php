<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\Response as IlluminateResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiController extends Controller
{

  protected $statusCode = IlluminateResponse::HTTP_OK;

  /**
   * @return mixed
   */
  public function getStatusCode()
  {
    return $this->statusCode;
  }

  /**
   * @param mixed $statusCode
   *
   * @return $this
   */
  public function setStatusCode($statusCode)
  {
    $this->statusCode = $statusCode;

    return $this;
  }


  public function canConnectedUserEditElement($id)
  {
    $user = JWTAuth::parseToken()->toUser();

    return $user->id === $id;
  }

  /**
   * @param       $data    - data to send trough the API
   * @param array $headers - optional headers for the HTTP Response
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function respond($data = [], $headers = [])
  {
    return response()->json($data, $this->getStatusCode(), $headers);
  }

  /**
   * @param $message
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function respondWithError($message)
  {
    return $this->respond([
        'message' => $message,
    ]);
  }

  public function respondWithPagination(LengthAwarePaginator $items, $data)
  {
    $response = array_merge($data, [
        'paginate' => [
            'total_count'  => $items->total(),
            'total_pages'  => ceil($items->total() / $items->perPage()),
            'current_page' => $items->currentPage(),
            'limit'        => $items->perPage()
        ]
    ]);

    return $this->respond($response);
  }


  /**
   * @param array $data
   *
   * @return mixed
   */
  public function respondCreated(Array $data)
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_CREATED)
        ->respond($data);
  }

  /**
   *
   * @return mixed
   */
  public function respondNoContent()
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_NO_CONTENT)
        ->respond();
  }


  public function respondNotFound($message = 'Not Found !')
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_NOT_FOUND)
        ->respondWithError($message);
  }

  /**
   * @param string $message
   *
   * @return \Illuminate\Http\JsonResponse
   */
  public function respondInternalError($message = 'Internal Error !')
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_INTERNAL_SERVER_ERROR)
        ->respondWithError($message);
  }

  /**
   * @param string $message
   *
   * @return mixed
   */
  public function respondFailedValidation($message = 'Parameters failed validation')
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_BAD_REQUEST)
        ->respondWithError($message);
  }

  /**
   * @param string $message
   *
   * @return mixed
   */
  public function respondUnauthorized($message = 'Invalid credentials')
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_UNAUTHORIZED)
        ->respondWithError($message);
  }

  /**
   * @param string $message
   *
   * @return mixed
   */
  public function respondForbidden($message = 'Forbidden')
  {
    return $this
        ->setStatusCode(IlluminateResponse::HTTP_FORBIDDEN)
        ->respondWithError($message);
  }
}
