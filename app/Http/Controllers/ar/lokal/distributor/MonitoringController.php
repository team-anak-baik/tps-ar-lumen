<?php

namespace app\Http\Controllers\ar\lokal\distributor;

use Illuminate\Http\{Request, JsonResponse};
use App\Models\ar\lokal\distributor\monitoring;
use App\Http\Controllers\Controller;

class MonitoringController extends Controller
{
    protected $odModel;

    public function __construct()
    {
        $this->odModel = new monitoring();
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $step = $request->input('step');
            $perPage = $request->input('perPage');
            $type = $request->input('type');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $todos = $this->odModel->getData($step, $type, null, null, $request->userid, $perPage, $startDate, $endDate);
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

    public function getReceipt(Request $request): JsonResponse
    {
        try {
            $code = $request->input('code');
            // $type = $request->input('type');
            $todos = $this->odModel->getReceipt($code, $request->userid);
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

    public function countData(Request $request): JsonResponse
    {
        try {
            $month = $request->input('month');
            $todos = $this->odModel->countData($request->userid, $month, null);
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

    public function searchData(Request $request): JsonResponse
    {
        try {
            $keyword = $request->input('keyword');
            $perPage = $request->input('perPage');
            $todos = $this->odModel->searchData($keyword, 'lengkap', $request->userid, $perPage);
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
