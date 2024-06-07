<?php

namespace App\Models\ar\server1\wms_tps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class order extends Model
{
    protected $connection = 'connection_third';
    protected $table = 'wms_tps.order';
    protected $connThird, $connFifth;

    public function __construct()
    {
        $this->connThird = DB::connection('connection_third');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    {
        try {
            if (!Schema::connection('connection_fifth')->hasTable('tempt_so_wms' . $userid)) {
                // Truncate the table if it exists
                Schema::connection('connection_fifth')->create('tempt_so_wms' . $userid, function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('baseEntry')->nullable();
                    $table->integer('docEntry')->nullable();
                    $table->integer('orderNo')->nullable();
                    $table->date('orderDate')->nullable();
                    $table->integer('orderqty')->nullable();
                });
            }
            $queryBase = $this->connThird->table($this->connThird->raw('(
                SELECT o1.baseEntry baseEntry,o1.docEntry,o.docNum orderNo,o.docDate orderDate, SUM(o1.quantity) orderqty FROM wms_tps.order o
                INNER JOIN wms_tps.orderdetail1 o1 ON o.docEntry=o1.docEntry
                WHERE o1.baseType=28
                GROUP BY o1.baseEntry,o1.docEntry) AS so_wms'))
                ->select('so_wms.baseEntry', 'so_wms.docEntry', 'so_wms.orderNo', 'so_wms.orderDate', 'so_wms.orderqty')
                ->whereBetween('so_wms.orderDate', [$startDate, $endDate])
                ->orderBy('so_wms.orderDate', 'DESC')
                ->get();

            foreach ($queryBase as $data) {
                $cekData = $this->connFifth->table('tempt_so_wms' . $userid)->where('orderNo', $data->orderNo)->first();
                if (empty($cekData)) {
                    $this->connFifth->table('tempt_so_wms' . $userid)->insert([
                        'baseEntry' => $data->baseEntry,
                        'docEntry' => $data->docEntry,
                        'orderNo' => $data->orderNo,
                        'orderDate' => $data->orderDate,
                        'orderqty' => $data->orderqty,
                    ]);
                }
            }

            $temporaryData = $this->connFifth->table('tempt_so_wms' . $userid)->paginate(10000);
            return $temporaryData;
            // return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
