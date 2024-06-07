<?php

namespace App\Http\Controllers\ar\server1\wms_tps;

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\FacadesURL;
use Illuminate\Support\Facades\DB;

use App\Models\distributor\deliverydetail;
use App\Http\Controllers\Controller;

class DeliveryDetailController extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        try {
            $todos = deliverydetail::paginate(10); 
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
