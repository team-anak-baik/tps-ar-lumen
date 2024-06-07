<?php

namespace App\Http\Controllers\ar\server1\tps;

use Illuminate\Http\{Request, JsonResponse};
use App\Models\distributor\order_customer_detail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OrderDetailController extends Controller
{
    protected $odModel;

    public function __construct()
    {
        $this->odModel = new order_customer_detail();
    }

    public function exportDataToJSON(Request $request)
    {
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 50);
        $batch = $request->input('batch', 1);
        $filePath = base_path('public/json/order_customer_detail/new_order_customer_detail_batch_' . $batch . '.json');

        try {
            $rows = order_customer_detail::whereYear('uploaded_at', 2024)->offset($offset)->limit($limit)->get();

            if ($rows->isEmpty()) {
                return response()->json(["message" => "Data empty"], 404);
            }

            $data = $rows->toArray();

            $existingData = [];

            if (file_exists($filePath)) {
                $existingData = json_decode(file_get_contents($filePath), true);
                $existingData = is_array($existingData) ? $existingData : [];
            }

            $mergedData = array_merge($existingData, $data);

            file_put_contents($filePath, json_encode($mergedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

            return response()->json(['count' => count($mergedData)], 200);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    public function countRow(Request $request)
    {
        $directory = base_path('public/json/order_customer_detail');
        $filesToDelete = glob($directory . '/*new_*');
        foreach ($filesToDelete as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        $totalRows = order_customer_detail::whereYear('uploaded_at', 2024)->count();
        return response()->json([$totalRows], 200);
    }


    public function refresh(Request $request)
    {
        $batch = $request->input('batch', 1);

        $oldFilePath = base_path('public/json/order_customer_detail/order_customer_detail_batch_' . $batch . '.json');
        $newFilePath = base_path('public/json/order_customer_detail/new_order_customer_detail_batch_' . $batch . '.json');

        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }

        if (file_exists($newFilePath)) {
            rename($newFilePath, $oldFilePath);
        }

        return response()->json(["Success"], 200);
    }

    function countFiles()
    {
        $directory = base_path('public/json/order_customer_detail');
        $files = glob($directory . '/*');

        $count = 0;

        foreach ($files as $file) {
            if (is_file($file) && strpos(basename($file), 'new_') === false) {
                $count++;
            }
        }

        return $count;
    }

    public function paginateFromJson(Request $request)
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 10);
        $batchCount = (int) $this->countFiles();
        $cacheKey = "total_data_count";

        try {
            $total = Cache::remember($cacheKey, Carbon::now()->addMinutes(10), function() use ($batchCount) {
                return $this->countTotalData($batchCount);
            });

            $data = $this->getData($batchCount, $page, $perPage);

            $paginatedData = new LengthAwarePaginator(
                $data,
                $total,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

            return response()->json($paginatedData, 200);

        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()], 500);
        }
    }

    private function getData1($batchCount, $page, $perPage)
    {
        $data = [];
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;
        $currentCount = 0;

        for ($batch = 1; $batch <= $batchCount; $batch++) {
            $filePath = base_path('public/json/order_customer_detail/order_customer_detail_batch_' . $batch . '.json');

            if (!file_exists($filePath)) {
                continue; 
            }

            $fileData = json_decode(file_get_contents($filePath), true);

            if (!is_array($fileData)) {
                continue;
            }

            $fileDataCount = count($fileData);
            if ($currentCount + $fileDataCount <= $start) {
                $currentCount += $fileDataCount;
                continue;
            }

            foreach ($fileData as $item) {
                if ($currentCount >= $start && $currentCount < $end) {
                    $data[] = $item;
                }
                $currentCount++;

                if ($currentCount >= $end) {
                    break 2;
                }
            }
        }

        return $data;
    }

    private function countTotalData($batchCount)
    {
        $total = 0;
        for ($batch = 1; $batch <= $batchCount; $batch++) {
            $filePath = base_path('public/json/order_customer_detail/order_customer_detail_batch_' . $batch . '.json');

            if (!file_exists($filePath)) {
                continue;
            }

            $fileData = json_decode(file_get_contents($filePath), true);

            if (!is_array($fileData)) {
                continue;
            }

            $total += count($fileData);
        }
        return $total;
    }

    private function getData($batchCount, $page, $perPage)
    {
        $data = [];
        $start = ($page - 1) * $perPage;
        $end = $start + $perPage;
        $currentCount = 0;

        for ($batch = 1; $batch <= $batchCount; $batch++) {
            $filePath = base_path('public/json/order_customer_detail/order_customer_detail_batch_' . $batch . '.json');

            if (!file_exists($filePath)) {
                continue;
            }

            $fileData = json_decode(file_get_contents($filePath), true);

            if (!is_array($fileData)) {
                continue;
            }

            $fileDataCount = count($fileData);
            if ($currentCount + $fileDataCount <= $start) {
                $currentCount += $fileDataCount;
                continue;
            }

            $customerFilePath = base_path('public/json/order_customer/order_customer_batch_' . $batch . '.json');
            $customerData = [];
            if (file_exists($customerFilePath)) {
                $customerData = json_decode(file_get_contents($customerFilePath), true);
            }

            foreach ($fileData as $item) {
                if ($currentCount >= $start && $currentCount < $end) {
                    $customer = array_filter($customerData, function($customerItem) use ($item) {
                        return $customerItem['id'] == $item['orderId'];
                    });
                    if (!empty($customer)) {
                        $mergedItem = array_merge($item, reset($customer));
                        $data[] = $mergedItem;
                    }
                }
                $currentCount++;

                if ($currentCount >= $end) {
                    break 2;
                }
            }
        }

        return $data;
    }

    
}
