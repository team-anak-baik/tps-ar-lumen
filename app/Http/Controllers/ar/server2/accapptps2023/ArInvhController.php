<?php

namespace App\Http\Controllers\ar\server2\accapptps2023;

use Illuminate\Http\{Request, JsonResponse};
use App\Models\ar\server2\accapptps2023\ar_invh;
use App\Http\Controllers\Controller;

class ArInvhController extends Controller
{
    protected $dlModel;

    public function __construct()
    {
        $this->dlModel = new ar_invh();
    }

    public function getData(Request $request): JsonResponse
    // public function getData(): JsonResponse
    {
        try {
            $startDate = $request->input('start_date') . ' 00:00:00';
            $endDate = $request->input('end_date') . ' 00:00:00';
            $todos = $this->dlModel->getData($startDate, $endDate, $request->userid);
            // $todos = $this->dlModel->getData();
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
