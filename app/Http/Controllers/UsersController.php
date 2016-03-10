<?php

namespace App\Http\controllers;

use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Transformers\UserTransformer;

class UsersController extends ApiController
{
  protected $userTransformer;
  protected $userRepository;

  function __construct(UserRepository $userRepository, UserTransformer $userTransformer)
  {
    $this->middleware('jwt.auth', ['except' => ['store']]);

    $this->userRepository = $userRepository;
    $this->userTransformer = $userTransformer;
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   *
   * @return Response
   */
  public function index(Request $request)
  {
    $users = $this->userRepository->filter($request->all());

    return $this->respondWithPagination($users, [
        'items' => $this->userTransformer->transformCollection($users->items())
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   *
   * @return Response
   */
  public function store(Request $request)
  {
    $inputs = $request->all();
    $validation = $this->userRepository->isValidForCreation('App\Models\User', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $inputs['password'] = bcrypt($request->input('password'));

    $createdUser = $this->userRepository->create($inputs);

    return $this->respondCreated($this->userTransformer->fullTransform($createdUser));
  }


  /**
   * Display the specified resource.
   *
   * @param Request $request
   * @param int     $id
   *
   * @return Response
   */
  public function show(Request $request, $id)
  {
    $user = $this->userRepository->find($id);

    if (!$user) {
      return $this->respondNotFound('User does not exist.');
    }


    return $this->respond($this->userTransformer->fullTransform($user));
  }


  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   *
   * @param int     $id
   *
   * @return Response
   */
  public function update(Request $request, $id)
  {
    $user = $this->userRepository->find($id);
    $inputs = $request->all();
    $inputs['id'] = $id;

    if (!$user) {
      return $this->respondNotFound('User does not exist.');
    }
    if (!$this->canConnectedUserEditElement($user['id'])) {
      return $this->respondForbidden();
    }

    $validation = $this->userRepository->isValidForUpdate('App\Models\User', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    if ($password = $request->input('password')) {
      $inputs['password'] = bcrypt($password);
    }

    $updatedUser = $this->userRepository->update($id, $inputs);

    return $this->respond($this->userTransformer->fullTransform($updatedUser));
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param int     $id
   *
   * @return Response
   */
  public function destroy(Request $request, $id)
  {
    $user = $this->userRepository->find($id);

    if (!$user) {
      return $this->respondNotFound('User does not exist.');
    }

    if (!$this->canConnectedUserEditElement($user['id'])) {
      return $this->respondForbidden();
    }

    $this->userRepository->delete($id);

    return $this->respondNoContent();
  }
}
