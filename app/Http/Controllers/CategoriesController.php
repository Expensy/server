<?php

namespace App\Http\controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Response;
use APp\Transformers\CategoryTransformer;
use Underscore\Types\Arrays;

class CategoriesController extends ApiController
{
  protected $categoryTransformer;
  protected $categoryRepository;
  protected $userRepository;

  function __construct(CategoryTransformer $categoryTransformer, CategoryRepository $categoryRepository, UserRepository $userRepository)
  {
    $this->beforeFilter('jwt.auth');

    $this->categoryTransformer = $categoryTransformer;
    $this->categoryRepository = $categoryRepository;
    $this->userRepository = $userRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @param null $userId
   *
   * @return Response
   */
  public function index($userId = null)
  {
    if (!is_null($userId)) {
      $user = $this->userRepository->find($userId);

      if (!$user) {
        return $this->respondNotFound('User does not exist.');
      }
    }

    $filters = Arrays::merge(Input::all(), ['userId' => $userId]);
    $categorys = $this->categoryRepository->filter($filters);

    return $this->respondWithPagination($categorys, [
        'data' => $this->categoryTransformer->transformCollection($categorys->all())
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @return Response
   */
  public function store()
  {
    $inputs = Input::all();

    $validation = $this->categoryRepository->isValidForCreation('Category', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $inputs['user_id'] = Auth::user()->id;
    $createdCategory = $this->categoryRepository->create($inputs);

    $response = [
        'data' => $this->categoryTransformer->fullTransform($createdCategory)
    ];

    return $this->respondCreated($response);
  }


  /**
   * Display the specified resource.
   *
   * @param  int $id
   *
   * @return Response
   */
  public function show($id)
  {
    $category = $this->categoryRepository->find($id);

    if (!$category) {
      return $this->respondNotFound('Category does not exist.');
    }

    return $this->respond([
        'data' => $this->categoryTransformer->fullTransform($category)
    ]);
  }


  /**
   * Update the specified resource in storage.
   *
   * @param  int $id
   *
   * @return Response
   */
  public function update($id)
  {
    $category = $this->categoryRepository->find($id);
    $inputs = Input::all();
    $inputs['id'] = $id;

    if (!$category) {
      return $this->respondNotFound('Category does not exist.');
    }

    if (!$this->canConnectedUserEditElement($category['user_id'])) {
      return $this->respondForbidden();
    }
    $validation = $this->categoryRepository->isValidForUpdate('Category', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $inputs['user_id'] = Auth::user()->id;
    $updatedCategory = $this->categoryRepository->update($id, $inputs);

    $response = [
        'data' => $this->categoryTransformer->fullTransform($updatedCategory)
    ];

    return $this->respond($response);
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param  int $id
   *
   * @return Response
   */
  public function destroy($id)
  {
    $category = $this->categoryRepository->find($id);

    if (!$category) {
      return $this->respondNotFound('Category does not exist.');
    }

    if (!$this->canConnectedUserEditElement($category['user_id'])) {
      return $this->respondForbidden();
    }

    $this->categoryRepository->delete($id);

    return $this->respondNoContent();
  }
}
