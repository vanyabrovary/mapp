<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\User;

/**
 * @param Request $request
 * @return JsonResponse
 */
class UserController extends Controller
{
    public function get(Request $request)
    {
        return response()->json([
            'status' => 'Good',
            'data' => User::all()
        ]);
    }
}
