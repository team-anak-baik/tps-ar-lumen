<?php
namespace App\Models\ar\server1\tps;

use Illuminate\Database\Eloquent\Model;

class deliverydetail1 extends Model
{
    protected $connection = 'connection_second';
    protected $table = 'tps.deliverydetail';

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
            'docEntry' => $item->docEntry,
            'docNum' => $item->docNum,
            'printed' => $item->printed,
            'docStatus' => $item->docStatus,
            'docDate' => $item->docDate,
            'docDueDate' => $item->docDueDate,
            'whsCode' => $item->whsCode,
            'priceCode' => $item->priceCode,
            'custmrCode' => $item->custmrCode,
            'shipToCode' => $item->shipToCode,
            'billToCode' => $item->billToCode,
            'slPrsnCode' => $item->slPrsnCode,
            'clctrCode' => $item->clctrCode,
            'taxPercent' => $item->taxPercent,
            'totalAmount' => $item->totalAmount,
            'discAmount' => $item->discAmount,
            'bfrTaxAmount' => $item->bfrTaxAmount,
            'taxAmount' => $item->taxAmount,
            'aftTaxAmount' => $item->aftTaxAmount,
            'pkpSts' => $item->pkpSts,
            'npwpNo' => $item->npwpNo,
            'npwpDate' => $item->npwpDate,
            'receiptNo' => $item->receiptNo,
            'discSumP1' => $item->discSumP1,
            'discSumP2' => $item->discSumP2,
            'discSumP3' => $item->discSumP3,
            'paidToDate' => $item->paidToDate,
            'ref1' => $item->ref1,
            'ref2' => $item->ref2,
            'comments' => $item->comments,
            'objType' => $item->objType,
            'termCode' => $item->termCode,
            'printCounter' => $item->printCounter,
            'auditDate' => $item->auditDate,
            'auditUser' => $item->auditUser,
            'backupSts' => $item->backupSts,
            'taxReport' => $item->taxReport,
            'rackSts' => $item->rackSts,
            'docnumout' => $item->docnumout,
            'docentryout' => $item->docentryout,
            'expsts' => $item->expsts,
            'tolsts' => $item->tolsts,
            'ctlsts' => $item->ctlsts,
            'suppnum' => $item->suppnum,
            'createdDate' => $item->createdDate,
            'cpysts' => $item->cpysts,
            'controled' => $item->controled,
            'cfmsts' => $item->cfmsts
        ];
    }

    return $itemsData;
}



    protected static function saveDataToJson($data)
    {
        $jsonData = json_encode($data);
        
        $filePath = base_path('public/json/deliverydetail.json');
        file_put_contents($filePath, $jsonData);
    }
}
