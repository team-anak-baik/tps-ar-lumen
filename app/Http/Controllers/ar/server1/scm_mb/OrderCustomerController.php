<?php

namespace App\Http\Controllers\ar\server1\scm_mb;

use Illuminate\Http\{Request, JsonResponse};
use App\Models\ar\server1\scm_mb\order_customer;
use App\Http\Controllers\Controller;

class OrderCustomerController extends Controller
{
    protected $odModel;

    public function __construct()
    {
        $this->odModel = new order_customer();
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $todos = $this->odModel->getData($startDate, $endDate, $request->userid);
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
