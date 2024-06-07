<?php

namespace App\Http\Controllers\ar\server1\tps;

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\FacadesURL;
use Illuminate\Support\Facades\DB;

use App\Models\distributor\delivery;
use App\Http\Controllers\Controller;

class DeliveryController extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        try {
            $todos = delivery::paginate(10); 
            return response()->json([
                'code' => 200,
                'status' => true,
                'data' => $todos
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 409);
        }
    }
}
