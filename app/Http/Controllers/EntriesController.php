<?php

namespace App\Http\Controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\EntryRepository;
use App\Repositories\ProjectRepository;
use App\Transformers\EntryTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EntriesController extends ApiController
{
  protected $entryTransformer;
  protected $entryRepository;
  protected $projectRepository;
  private $categoryRepository;

  function __construct(EntryRepository $entry, EntryTransformer $entryTransformer, ProjectRepository $projectRepository, CategoryRepository $categoryRepository) {
    $this->middleware('jwt.auth');
    $this->middleware('expensy.user');
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
   * @param int $projectId
   *
   * @return JsonResponse
   */
  public function index(Request $request, int $projectId) {
    $filters = array_merge($request->all(), ['project_id' => $projectId]);

    $validation = Validator::make($filters, [
      'project_id' => 'required',
      'start_date' => 'required',
      'end_date' => 'required'
    ]);

    if ($validation->fails()) {
      return $this->respondFailedValidation($validation->messages());
    }

    $entries = $this->entryRepository->filter($filters);
    $stats = $this->entryRepository->stats($filters);

    return $this->respondWithPagination($entries, [
      'items' => $this->entryTransformer->transformCollection($entries->items()),
      'stats' => $stats
    ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @param int $projectId
   * @return JsonResponse
   */
  public function store(Request $request, int $projectId) {
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
   * @param         $id
   *
   * @return JsonResponse
   * @internal param int $id
   *
   */
  public function show(Request $request, $id) {
    $entry = $this->entryRepository->find($id);

    if (!$entry) {
      return $this->respondNotFound('Entry does not exist.');
    }

    return $this->respond($this->entryTransformer->fullTransform($entry));
  }


  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param         $id
   *
   * @return JsonResponse
   * @internal param int $id
   *
   */
  public function update(Request $request, $id) {
    $entry = $this->entryRepository->find($id);
    $inputs = $request->all();
    $inputs['id'] = $id;
    $inputs['project_id'] = $entry->project->id;

    if (!$entry) {
      return $this->respondNotFound('Entry does not exist.');
    }
    $validation = $this->entryRepository->isValidForUpdate('App\Models\Entry', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $updatedEntry = $this->entryRepository->update($id, $inputs);

    return $this->respond($this->entryTransformer->fullTransform($updatedEntry));
  }


  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param         $id
   *
   * @return JsonResponse
   * @internal param int $id
   *
   */
  public function destroy(Request $request, $id) {
    $entry = $this->entryRepository->find($id);

    if (!$entry) {
      return $this->respondNotFound('Entry does not exist.');
    }

    $this->entryRepository->delete($id);

    return $this->respondNoContent();
  }
}
