<?php

namespace App\Http\controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\ProjectRepository;
use App\Transformers\CategoryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Underscore\Types\Arrays;

class CategoriesController extends ApiController {
  protected $categoryTransformer;
  protected $categoryRepository;
  protected $projectRepository;

  function __construct(CategoryRepository $categoryRepository, CategoryTransformer $categoryTransformer, ProjectRepository $projectRepository) {
    $this->middleware('jwt.auth');
    $this->middleware('expensy.project');

    $this->categoryRepository = $categoryRepository;
    $this->categoryTransformer = $categoryTransformer;
    $this->projectRepository = $projectRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   * @param int     $projectId
   *
   * @return Response
   */
  public function index(Request $request, int $projectId) {
    $filters = Arrays::merge($request->all(), ['project_id' => $projectId]);
    $categories = $this->categoryRepository->filter($filters);

    return $this->respondWithPagination($categories, [
      'items' => $this->categoryTransformer->transformCollection($categories->items())
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

    $validation = $this->categoryRepository->isValidForCreation('App\Models\Category', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $createdCategory = $this->categoryRepository->create($inputs);

    return $this->respondCreated($this->categoryTransformer->fullTransform($createdCategory));
  }


  /**
   * Display the specified resource.
   *
   * @param Request $request
   * @param         $id
   *
   * @return Response
   * @internal param int $id
   *
   */
  public function show(Request $request, $id) {
    $category = $this->categoryRepository->find($id);

    if (!$category) {
      return $this->respondNotFound('Category does not exist.');
    }

    return $this->respond($this->categoryTransformer->fullTransform($category));
  }


  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param         $id
   *
   * @return Response
   * @internal param int $id
   *
   */
  public function update(Request $request, $id) {
    $category = $this->categoryRepository->find($id);
    $inputs = $request->all();
    $inputs['id'] = $id;

    if (!$category) {
      return $this->respondNotFound('Category does not exist.');
    }

    $validation = $this->categoryRepository->isValidForUpdate('App\Models\Category', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $updatedCategory = $this->categoryRepository->update($id, $inputs);

    return $this->respond($this->categoryTransformer->fullTransform($updatedCategory));
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param         $categoryId
   *
   * @return Response
   * @internal param int $id
   *
   */
  public function destroy(Request $request, $categoryId) {

    //TODO you have to make sure you have at least one category for a project
    //TODO what to do with Entries relying on this category ??

    //    $category = $this->categoryRepository->find($categoryId);
    //
    //    if (!$category) {
    //      return $this->respondNotFound('Category does not exist.');
    //    }
    //
    //    $this->categoryRepository->delete($categoryId);
    //
    //    return $this->respondNoContent();
  }
}
