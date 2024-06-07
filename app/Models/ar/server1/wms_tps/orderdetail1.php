<?php
namespace App\Models\ar\server1\wms_tps;

use Illuminate\Database\Eloquent\Model;

class wms_orderdetail1 extends Model
{
    protected $connection = 'connection_third';
    protected $table = 'wms_tps.orderdetail1';

    public static function getDataAndSaveToJson()
    {
        $totalData = self::count();
        $dataPerStep = 50;
        $totalSteps = ceil($totalData / $dataPerStep);
        $allData = [];
        for ($i = 0; $i < $totalSteps; $i++) {
            $offset = $i * $dataPerStep;
            $items = self::offset($offset)->limit($dataPerStep)->get();
            $itemsData = self::processData($items);
            $allData = array_merge($allData, $itemsData);
            self::saveDataToJson($allData);
        }       
    }

    protected static function processData($items)
{
    $itemsData = [];

    foreach ($items as $item) {
        $itemsData[] = [
            'docEntry' => $item['docEntry'],
            'lineNum' => $item['lineNum'],
            'baseRef' => $item['baseRef'],
            'baseType' => $item['baseType'],
            'baseEntry' => $item['baseEntry'],
            'baseLine' => $item['baseLine'],
            'lineStatus' => $item['lineStatus'],
            'itemCode' => $item['itemCode'],
            'quantity' => $item['quantity'],
            'openQty' => $item['openQty'],
            'cost' => $item['cost'],
            'price' => $item['price'],
            'discPrcnt1' => $item['discPrcnt1'],
            'discPrcnt2' => $item['discPrcnt2'],
            'discPrcnt3' => $item['discPrcnt3'],
            'isItemBns' => $item['isItemBns'],
            'lineTotal' => $item['lineTotal'],
            'whsCode' => $item['whsCode'],
            'baseDocNum' => $item['baseDocNum'],
            'visOrder' => $item['visOrder'],
            'baseQty' => $item['baseQty'],
            'baseOpnQty' => $item['baseOpnQty'],
            'objType' => $item['objType'],
            'pickQty' => $item['pickQty'],
            'backupSts' => $item['backupSts'],
            'priceCode' => $item['priceCode'],
        ];
    }

    return $itemsData;
}

    protected static function saveDataToJson($data)
    {
        $jsonData = json_encode($data);
        
        $filePath = base_path('public/json/wms_orderdetail1.json');
        file_put_contents($filePath, $jsonData);
    }
}
