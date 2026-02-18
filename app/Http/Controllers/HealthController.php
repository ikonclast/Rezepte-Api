<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/health',
        summary: 'Health Check',
        tags: ['System'],
        responses: [
            new OA\Response(response: 200, description: 'OK')
        ]
    )]
    public function __invoke(): JsonResponse
    {
        return response()->json(['status' => 'ok']);
    }
}
