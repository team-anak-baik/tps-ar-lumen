<?php

namespace App\Http\Controllers\ar\server2\accapptps2023;

use Illuminate\Http\{Request, JsonResponse};
use App\Models\ar\server2\accapptps2023\cb_batchh;
use App\Http\Controllers\Controller;

class CbBatchhController extends Controller
{
    protected $dlModel;

    public function __construct()
    {
        $this->dlModel = new cb_batchh();
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $s = explode('-', $request->input('start_date')); //2024-03-31
            $e = explode('-', $request->input('end_date')); //2024-03-31
            $startDate = $s[2] . $s[1] . $s[0]; //31032024
            $endDate = $e[2] . $e[1] . $e[0]; //31032024
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
