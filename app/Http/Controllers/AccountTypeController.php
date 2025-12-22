<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\AccountTypeResource;
use App\Repositories\AccountTypeRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountTypeController extends Controller
{
    public function __construct(protected AccountTypeRepository $accountTypeRepository) {}

    public function index(Request $request)
    {
        $accountTypes = $this->accountTypeRepository->all();
        $result = AccountTypeResource::collectionWithMeta($accountTypes);

        return ApiResponse::success(
            'Account Types retrieved successfully.',
            $result['data'],
            Response::HTTP_OK,
            $result['meta'] ?? [],
            $result['links'] ?? []
        );
    }
}
