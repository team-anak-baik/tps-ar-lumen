<?php

namespace App\Http\Controllers\ar\server1\scm_mb;

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\FacadesURL;
use Illuminate\Support\Facades\DB;

use App\Models\distributor\release_order_customer_detail;
use App\Http\Controllers\Controller;

class ReleaseOrderCustomerDetailController extends Controller
{
    public function getData(Request $request): JsonResponse
    {
        try {
            $todos = release_order_customer_detail::orderBy('id')->paginate(10); 
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
