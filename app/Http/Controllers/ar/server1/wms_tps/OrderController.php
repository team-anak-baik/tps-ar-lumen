<?php

namespace App\Http\Controllers\ar\server1\wms_tps;

use Illuminate\Http\{Request, JsonResponse};

use App\Models\ar\server1\wms_tps\order;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    protected $dlModel;

    public function __construct()
    {
        $this->dlModel = new order();
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $todos = $this->dlModel->getData($startDate, $endDate, $request->userid);
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
