<?php

namespace Tests\Unit\Helpers;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response()
    {
        $response = ApiResponse::success('Test success message', ['foo' => 'bar'], Response::HTTP_OK);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Test success message', $data['message']);
        $this->assertFalse($data['error']);
        $this->assertEquals(Response::HTTP_OK, $data['code']);
        $this->assertEquals(['foo' => 'bar'], $data['results']);
    }

    public function test_error_response()
    {
        $response = ApiResponse::error('Test error message', ['foo' => 'bar'], Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertEquals('Test error message', $data['message']);
        $this->assertTrue($data['error']);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $data['code']);
        $this->assertEquals(['foo' => 'bar'], $data['errors']);
    }
}
