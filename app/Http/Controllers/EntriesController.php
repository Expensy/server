<?php

namespace App\Http\controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\EntryRepository;
use App\Repositories\ProjectRepository;
use App\Transformers\EntryTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Underscore\Types\Arrays;

class EntriesController extends ApiController
{
  protected $entryTransformer;
  protected $entryRepository;
  protected $projectRepository;
  private $categoryRepository;

  function __construct(EntryRepository $entry, EntryTransformer $entryTransformer, ProjectRepository $projectRepository, CategoryRepository $categoryRepository)
  {
    $this->middleware('jwt.auth');
    $this->middleware('expensy.project');

    $this->entryRepository = $entry;
    $this->entryTransformer = $entryTransformer;
    $this->projectRepository = $projectRepository;
    $this->categoryRepository = $categoryRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   * @param int     $projectId
   *
   * @return Response
   */
  public function index(Request $request, int $projectId)
  {
    $filters = Arrays::merge($request->all(), ['project_id' => $projectId]);
    $entries = $this->entryRepository->filter($filters);

    return $this->respondWithPagination($entries, [
        'items' => $this->entryTransformer->transformCollection($entries->items())
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @param int     $projectId
   *
   * @return Response
   */
  public function store(Request $request, int $projectId)
  {
    $inputs = $request->all();
    $inputs['project_id'] = $projectId;

    $validation = $this->entryRepository->isValidForCreation('App\Models\Entry', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $createdEntry = $this->entryRepository->create($inputs);

    return $this->respondCreated($this->entryTransformer->fullTransform($createdEntry));
  }


  /**
   * Display the specified resource.
   *
   * @param Request $request
   * @param         $projectId
   * @param         $entryId
   *
   * @return Response
   * @internal param int $id
   *
   */
  public function show(Request $request, $projectId, $entryId)
  {
    $entry = $this->entryRepository->find($entryId);

    if (!$entry) {
      return $this->respondNotFound('Entry does not exist.');
    }

    return $this->respond($this->entryTransformer->fullTransform($entry));
  }


  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param         $projectId
   * @param         $entryId
   *
   * @return Response
   * @internal param int $id
   *
   */
  public function update(Request $request, $projectId, $entryId)
  {
    $entry = $this->entryRepository->find($entryId);
    $inputs = $request->all();
    $inputs['project_id'] = $projectId;
    $inputs['id'] = $entryId;

    if (!$entry) {
      return $this->respondNotFound('Entry does not exist.');
    }
    $validation = $this->entryRepository->isValidForUpdate('App\Models\Entry', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $updatedEntry = $this->entryRepository->update($entryId, $inputs);

    return $this->respond($this->entryTransformer->fullTransform($updatedEntry));
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param         $projectId
   * @param         $entryId
   *
   * @return Response
   * @internal param int $id
   *
   */
  public function destroy(Request $request, $projectId, $entryId)
  {
    $entry = $this->entryRepository->find($entryId);

    if (!$entry) {
      return $this->respondNotFound('Entry does not exist.');
    }

    $this->entryRepository->delete($entryId);

    return $this->respondNoContent();
  }
}
