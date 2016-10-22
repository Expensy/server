<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Repositories\CategoryRepository;
use App\Repositories\ProjectRepository;
use App\Repositories\UserRepository;
use App\Transformers\ProjectTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProjectsController extends ApiController
{
  protected $projectRepository;
  protected $projectTransformer;
  protected $userRepository;
  protected $categoryRepository;

  function __construct(ProjectRepository $projectRepository, ProjectTransformer $projectTransformer, UserRepository $userRepository, CategoryRepository $categoryRepository) {
    $this->middleware('jwt.auth');
    $this->middleware('expensy.user');

    $this->projectRepository = $projectRepository;
    $this->projectTransformer = $projectTransformer;
    $this->userRepository = $userRepository;
    $this->categoryRepository = $categoryRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   *
   * @return Response
   */
  public function index(Request $request) {
    $projects = $this->projectRepository->filter($request->all());

    return $this->respondWithPagination($projects, [
      'items' => $this->projectTransformer->transformCollection($projects->items())
    ]);
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   *
   * @return Response
   */
  public function indexArchived(Request $request) {
    $projects = Project::onlyTrashed()->paginate();

    return $this->respondWithPagination($projects, [
      'items' => $this->projectTransformer->transformCollection($projects->items())
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
    $validation = $this->projectRepository->isValidForCreation('App\Models\Project', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $createdProject = $this->projectRepository->create($inputs);

    return $this->respondCreated($this->projectTransformer->fullTransform($createdProject));
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
    $project = $this->projectRepository->find($id);

    if (!$project) {
      return $this->respondNotFound('Project does not exist.');
    }
    if (!$project->isAccessibleByConnectedUser()) {
      return $this->respondForbidden();
    }

    return $this->respond($this->projectTransformer->fullTransform($project));
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
    $project = $this->projectRepository->find($id);
    $inputs = $request->all();

    if (!$project) {
      return $this->respondNotFound('Project does not exist.');
    }
    if (!$project->isAccessibleByConnectedUser()) {
      return $this->respondForbidden();
    }

    $validation = $this->projectRepository->isValidForUpdate('App\Models\Project', $inputs);

    if (!$validation->passes) {
      return $this->respondFailedValidation($validation->messages);
    }

    $updatedProject = $this->projectRepository->update($id, $inputs);

    return $this->respond($this->projectTransformer->fullTransform($updatedProject));
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
    $project = $this->projectRepository->find($id);

    if (!$project) {
      return $this->respondNotFound('Project does not exist.');
    }

    if (!$project->isAccessibleByConnectedUser()) {
      return $this->respondForbidden();
    }

    $this->projectRepository->delete($id);

    return $this->respondNoContent();
  }

  public function addMember(Request $request, $id, $userId) {
    $project = $this->projectRepository->find($id);
    $user = $this->userRepository->find($userId);

    if (!$project) {
      return $this->respondNotFound('Project does not exist.');
    }

    if (!$user) {
      return $this->respondNotFound('User does not exist.');
    }

    if (!$project->isAccessibleByConnectedUser()) {
      return $this->respondForbidden();
    }

    if ($project->users->contains('id', $userId)) {
      return $this->respondFailedValidation('User already in the project.');
    }

    $this->projectRepository->addUser($id, $userId);
    $updatedProject = $this->projectRepository->find($id);

    return $this->respond($this->projectTransformer->fullTransform($updatedProject));
  }

  public function removeMember(Request $request, $id, $userId) {
    $project = $this->projectRepository->find($id);
    $user = $this->userRepository->find($userId);

    if (!$project) {
      return $this->respondNotFound('Project does not exist.');
    }

    if (!$user) {
      return $this->respondNotFound('User does not exist.');
    }

    if (!$project->isAccessibleByConnectedUser()) {
      return $this->respondForbidden();
    }

    $this->projectRepository->removeUser($id, $userId);

    return $this->respond($this->projectTransformer->fullTransform($project));
  }
}
