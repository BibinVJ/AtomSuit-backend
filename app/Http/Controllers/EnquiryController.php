<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\EnquiryRequest;
use App\Repositories\EnquiryRepository;
use App\Services\ContactService;

class EnquiryController extends Controller
{
    public function __construct(
        protected EnquiryRepository $enquiryRepository,
        protected ContactService $contactService
    ) {}

    public function store(EnquiryRequest $request)
    {
        $validated = $request->validated();

        $this->enquiryRepository->store($validated);
        $this->contactService->sendContactMail($validated);

        return ApiResponse::success('Thank you! Your message has been successfully sent.');
    }
}
