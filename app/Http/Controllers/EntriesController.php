<?php

use Arato\Push\PushService;
use Arato\Repositories\EntryRepository;
use Arato\Repositories\UserRepository;
use controllers\ApiController;
use Illuminate\Support\Facades\Response;
use Arato\Transformers\EntryTransformer;
use Underscore\Types\Arrays;

class EntriesController extends ApiController
{
    protected $entryTransformer;
    protected $entryRepository;
    protected $userRepository;

    function __construct(EntryTransformer $entryTransformer, EntryRepository $entryRepository, UserRepository $userRepository)
    {
        $this->beforeFilter('auth.basic');

        $this->entryTransformer = $entryTransformer;
        $this->entryRepository = $entryRepository;
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
        $entrys = $this->entryRepository->filter($filters);

        return $this->respondWithPagination($entrys, [
            'data' => $this->entryTransformer->transformCollection($entrys->all())
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

        $validation = $this->entryRepository->isValidForCreation('Entry', $inputs);

        if (!$validation->passes) {
            return $this->respondFailedValidation($validation->messages);
        }

        $inputs['user_id'] = Auth::user()->id;
        $createdEntry = $this->entryRepository->create($inputs);

        $response = [
            'data' => $this->entryTransformer->fullTransform($createdEntry)
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
        $entry = $this->entryRepository->find($id);

        if (!$entry) {
            return $this->respondNotFound('Entry does not exist.');
        }

        return $this->respond([
            'data' => $this->entryTransformer->fullTransform($entry)
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
        $entry = $this->entryRepository->find($id);
        $inputs = Input::all();
        $inputs['id'] = $id;

        if (!$entry) {
            return $this->respondNotFound('Entry does not exist.');
        }

        if (!$this->canConnectedUserEditElement($entry['user_id'])) {
            return $this->respondForbidden();
        }
        $validation = $this->entryRepository->isValidForUpdate('Entry', $inputs);

        if (!$validation->passes) {
            return $this->respondFailedValidation($validation->messages);
        }

        $inputs['user_id'] = Auth::user()->id;
        $updatedEntry = $this->entryRepository->update($id, $inputs);

        $response = [
            'data' => $this->entryTransformer->fullTransform($updatedEntry)
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
        $entry = $this->entryRepository->find($id);

        if (!$entry) {
            return $this->respondNotFound('Entry does not exist.');
        }

        if (!$this->canConnectedUserEditElement($entry['user_id'])) {
            return $this->respondForbidden();
        }

        $this->entryRepository->delete($id);

        return $this->respondNoContent();
    }
}
