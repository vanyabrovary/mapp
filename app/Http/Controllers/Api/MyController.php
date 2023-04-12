<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @param Request $request
 * @return JsonResponse
 */
class MyController extends Controller
{
    public function get(Request $request)
    {

        return response()->json([
            'status' => 'Good',
            'data' => ['My' => 'controller']
        ]);
    }
}
