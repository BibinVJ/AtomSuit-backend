<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\BoothRequest;
use App\Http\Resources\BoothResource;
use App\Models\Booth;
use App\Repositories\BoothRepository;
use App\Services\BoothService;
use Illuminate\Http\Request;

class BoothController extends Controller
{
    public function __construct(
        protected BoothRepository $boothRepo,
        protected BoothService $boothService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $booths = $this->boothRepo->all($request->get('per_page', 15));
        return ApiResponse::success('Booths fetched successfully.', BoothResource::paginated($booths));
    }

    /**
     * Display the specified resource.
     */
    public function show(Booth $booth)
    {
        $booth = $this->boothRepo->find($booth->id);
        return ApiResponse::success('Booth fetched successfully.', $booth);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BoothRequest $request)
    {
        $booth = $this->boothRepo->create($request->validated());
        return ApiResponse::success('Booth created successfully.', $booth);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BoothRequest $request, Booth $booth)
    {
        $booth = $this->boothRepo->update($booth, $request->validated());
        return ApiResponse::success('Booth updated successfully.', $booth);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booth $booth)
    {
        // $booth = $this->boothRepo->find($booth->id);

        $this->boothService->ensureBoothIsDeletable($booth);
        $this->boothRepo->delete($booth);

        return ApiResponse::success('Booth deleted successfully.');
    }
}
