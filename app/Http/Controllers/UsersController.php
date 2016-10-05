<?php

namespace App\Http\Controllers;

use App\Notifications\RegisteredUser;
use App\Repositories\UserRepository;
use App\Transformers\UserTransformer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request as Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class UsersController extends ApiController
{
  protected $userTransformer;
  protected $userRepository;

  function __construct(UserRepository $userRepository, UserTransformer $userTransformer) {
    $this->middleware('jwt.auth', ['except' => ['store']]);
    $this->middleware('expensy.user', ['except' => ['store']]);

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
  public function index(Request $request) {
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
  public function store(Request $request) {
    $inputs = $request->all();
    $validation = $this->userRepository->isValidForCreation('App\Models\User', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $inputs['password'] = bcrypt($request->input('password'));

    $hash = Hash::make(str_random(16));
    $hash = str_replace('?', '', $hash);
    $hash = str_replace('/', '', $hash);

    $inputs['confirmation_token'] = $hash;
    $createdUser = $this->userRepository->create($inputs);

    event(new Registered($user = $request));

    $createdUser->notify(new RegisteredUser());

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
  public function show(Request $request, $id) {
    if ($id === "current") {
      $user = JWTAuth::parseToken()->toUser();
    } else {
      $user = $this->userRepository->find($id);
    }

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
  public function update(Request $request, $id) {
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
  public function destroy(Request $request, $id) {
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
