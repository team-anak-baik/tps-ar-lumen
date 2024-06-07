<?php

namespace App\Http\Controllers\ar\server1\scm_mb;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\ar\server1\scm_mb\order_customer_detail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrderCustomerDetailController extends Controller
{
    protected $odModel;

    public function __construct()
    {
        $this->odModel = new order_customer_detail();
    }

    public function getData(): JsonResponse
    {
        try {
            $todos = $this->odModel->getData();
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
