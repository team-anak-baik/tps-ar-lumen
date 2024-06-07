<?php
namespace App\Models\ar\server2\accapptps2023;

use Illuminate\Database\Eloquent\Model;

class cb_batchsd extends Model
{
    protected $connection = 'connection_fourth';
    protected $table = 'accapptps2023.cb_batchsd';

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
            'id' => $item->id,
            'batchno' => $item->batchno,
            'entryno' => $item->entryno,
            'detailno' => $item->detailno,
            'subdetailno' => $item->subdetailno,
            'linests' => $item->linests,
            'docno' => $item->docno,
            'paymentno' => $item->paymentno,
            'doctype' => $item->doctype,
            'applamount' => $item->applamount,
            'discount' => $item->discount,
            'docdate' => $item->docdate,
            'prepayno' => $item->prepayno,
            'pono' => $item->pono,
            'sono' => $item->sono,
            'custcode' => $item->custcode,
            'adjtreff' => $item->adjtreff,
            'adjtdesc' => $item->adjtdesc,
            'adjtamount' => $item->adjtamount,
            'objtype' => $item->objtype,
            'entrydate' => $item->entrydate,
            'auditdate' => $item->auditdate,
            'audituser' => $item->audituser,
            'cmpnyid' => $item->cmpnyid,
        ];
    }

    return $itemsData;
}




    protected static function saveDataToJson($data)
    {
        $jsonData = json_encode($data);
        
        $filePath = base_path('public/json/cb_batchsd.json');
        file_put_contents($filePath, $jsonData);
    }
}
