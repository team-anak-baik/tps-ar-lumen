<?php

namespace App\Http\Controllers\ar\server2\accapptps2023;

use Illuminate\Http\{Request, JsonResponse};

use App\Models\ar\server2\accapptps2023\ar_invobl;
use App\Http\Controllers\Controller;

class ArInvoblController extends Controller
{
    protected $ar_invoblModel;
    public function __construct()
    {
        $this->ar_invoblModel = new ar_invobl();
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $cutOffDue = $request->input('cutOffDue');

            $data = $this->ar_invoblModel->getAgingAll($cutOffDue);

            // if ($type == "summary") {
            //     $data = $this->invoicesModel->getAgingSummary($cutOffDoc, $cutOffDue);
            // } elseif ($type == "detail") {
            //     $data = $this->invoicesModel->getAgingDetail($cutOffDoc, $cutOffDue);
            // }

            if ($data) {
                return response()->json([
                    "code" => 200,
                    "status" => true,
                    "data" => $data
                ], 200);
            } else {
                return response()->json([
                    "code" => 404,
                    "status" => false,
                    "message" => "Not found.",
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "code" => 500,
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function getInvoices(Request $request, $code): JsonResponse
    {
        try {
            $cutOffDue = $request->input('cutOffDue');

            $data = $this->ar_invoblModel->getAgingDetailCustomer($cutOffDue, $code);

            if ($data) {
                return response()->json([
                    "code" => 200,
                    "status" => true,
                    "data" => $data
                ], 200);
            } else {
                return response()->json([
                    "code" => 404,
                    "status" => false,
                    "message" => "Not found.",
                ], 404);
            }
        } catch (\Throwable $th) {
            return response()->json([
                "code" => 500,
                "status" => false,
                "message" => $th->getMessage(),
            ], 500);
        }
    }

    public function countData(Request $request): JsonResponse
    {
        try {
            $month = $request->input('month');

            if ($month == '00') {
                $month = date("n");
            }

            // $requisitions = $this->requisitionsModel->countData($month);
            // $purchaseOrders = $this->purchaseOrdersModel->countData($month);
            // $invoices = $this->invoicesModel->countData($month);
            // $paymentVouchers = $this->paymentVouchersModel->countData($month);
            $aging = $this->ar_invoblModel->getAging($month);


            $data = [
                // "requisitions" => $requisitions,
                // "purchaseOrders" => $purchaseOrders,
                // "invoices" => $invoices,
                // "paymentVouchers" => $paymentVouchers,
                "aging" => $aging,
            ];

            return response()->json([
                "code" => 200,
                "status" => true,
                "data" => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
