<?php
namespace App\Models\ar\server1\tps;

use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    protected $connection = 'connection_second';
    protected $table = 'tps.customer';

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
            'custmrCode' => $item->custmrCode,
            'custmrName' => $item->custmrName,
            'notes' => $item->notes,
            'balance' => $item->balance,
            'dNotesBal' => $item->dNotesBal,
            'ordersBal' => $item->ordersBal,
            'goodsissuesBal' => $item->goodsissuesBal,
            'credLimit' => $item->credLimit,
            'slPrsnCode' => $item->slPrsnCode,
            'clctrCode' => $item->clctrCode,
            'termCode' => $item->termCode,
            'discCode' => $item->discCode,
            'whsCode' => $item->whsCode,
            'locked' => $item->locked,
            'priceCode' => $item->priceCode,
            'objType' => $item->objType,
            'regDate' => $item->regDate,
            'taxInSameMonth' => $item->taxInSameMonth,
            'incTax' => $item->incTax,
            'grpOltTypeCode' => $item->grpOltTypeCode,
            'bpCustId' => $item->bpCustId,
            'pkpSts' => $item->pkpSts,
            'auditDate' => $item->auditDate,
            'auditUser' => $item->auditUser,
            'backupSts' => $item->backupSts,
            'custmrcodeprn' => $item->custmrcodeprn,
            'salessts' => $item->salessts,
            'bpcmpnyid' => $item->bpcmpnyid,
            'bpcustmrcode' => $item->bpcustmrcode,
            'posling' => $item->posling,
            'poslink' => $item->poslink,
        ];
    }

    return $itemsData;
}


    protected static function saveDataToJson($data)
    {
        $jsonData = json_encode($data);
        
        $filePath = base_path('public/json/customer.json');
        file_put_contents($filePath, $jsonData);
    }
}
