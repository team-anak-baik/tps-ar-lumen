<?php

namespace App\Models\ar\server1\scm_mb;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Throwable;
use Carbon\Carbon;

class order_customer extends Model
{
    protected $connection = 'connection_first';
    protected $table = 'scm_mb.order_customer';
    protected $connFirst, $connSecond;

    public function __construct()
    {
        $this->connFirst = DB::connection('connection_first');
        $this->connSecond = DB::connection('connection_second');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    {
        try {
            if (!Schema::connection('connection_fifth')->hasTable('tempt_oc' . $userid)) {
                // Truncate the table if it exists
                Schema::connection('connection_fifth')->create('tempt_oc' . $userid, function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('orderId')->nullable();
                    $table->string('custmrCode', 255)->nullable();
                    $table->string('custmrName', 255)->nullable();
                    $table->string('no_order', 255)->nullable();
                    $table->date('tgl_order')->nullable();
                    $table->string('qtyOrder', 255)->nullable();
                });
            }

            $queryBase = $this->connFirst->table($this->connFirst->raw('(
            SELECT oc.uploaded_at, c.custmrCode, c.custmrName, oc1.orderId, oc.no_order, oc.tgl_order, SUM(oc1.qtyOrder) qtyOrder FROM scm_mb.order_customer oc
            INNER JOIN scm_mb.order_customer_detail oc1 ON oc.id = oc1.orderId
            INNER JOIN tps.custoptsettdetail1 cod ON cod.ownerCode = oc.cust_code AND cod.optionalCode = 77
            INNER JOIN tps.customer c ON c.custmrCode = oc.cust_code
            WHERE oc.company = 4 AND oc.tipe = "SO" AND cod.optDtlCode = "01"
            GROUP BY oc1.orderId, oc.no_order) AS oc'))
                ->select('oc.orderId', 'oc.custmrCode', 'oc.custmrName', 'oc.no_order', 'oc.tgl_order', 'oc.qtyOrder')
                ->whereBetween('oc.tgl_order', [$startDate, $endDate])
                ->orderBy('oc.tgl_order', 'DESC')
                ->get();

            foreach ($queryBase as $data) {
                $cekData = $this->connFifth->table('tempt_oc' . $userid)->where('no_order', $data->no_order)->first();
                if (empty($cekData)) {
                    $this->connFifth->table('tempt_oc' . $userid)->insert([
                        'orderId' => $data->orderId,
                        'custmrCode' => $data->custmrCode,
                        'custmrName' => $data->custmrName,
                        'no_order' => $data->no_order,
                        'tgl_order' => $data->tgl_order,
                        'qtyOrder' => $data->qtyOrder,
                    ]);
                }
            }

            $temporaryData = $this->connFifth->table('tempt_oc' . $userid)->paginate(10000);
            return $temporaryData;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }


    // public function getData($startDate, $endDate)
    // {
    //     try {
    //         $this->connFifth->table('oc')->truncate();

    //         $queryBase = $this->connFirst->table($this->connFirst->raw('(
    //             SELECT oc.uploaded_at, c.custmrCode, c.custmrName, oc1.orderId, oc.no_order, oc.tgl_order, SUM(oc1.qtyOrder) qtyOrder FROM scm_mb.order_customer oc
    //             INNER JOIN scm_mb.order_customer_detail oc1 ON oc.id = oc1.orderId
    //             INNER JOIN tps.custoptsettdetail1 cod ON cod.ownerCode = oc.cust_code AND cod.optionalCode = 77
    //             INNER JOIN tps.customer c ON c.custmrCode = oc.cust_code
    //             WHERE oc.company = 4 AND oc.tipe = "SO" AND cod.optDtlCode = "01"
    //             GROUP BY oc1.orderId, oc.no_order) AS oc'))
    //             ->select('oc.orderId', 'oc.custmrCode', 'oc.custmrName', 'oc.no_order', 'oc.tgl_order', 'oc.qtyOrder')
    //             // ->whereBetween('oc.uploaded_at', ['2024-04-01 00:00:00', '2024-04-30 00:00:00'])
    //             ->whereBetween('oc.uploaded_at', [$startDate, $endDate])
    //             ->orderBy('oc.tgl_order', 'DESC')
    //             ->get();

    //         foreach ($queryBase as $data) {
    //             $cekData = $this->connFifth->table('oc')->where('no_order', $data->no_order)->first();
    //             if (empty($cekData)) {
    //                 $this->connFifth->table('oc')->insert([
    //                     'orderId' => $data->orderId,
    //                     'custmrCode' => $data->custmrCode,
    //                     'custmrName' => $data->custmrName,
    //                     'no_order' => $data->no_order,
    //                     'tgl_order' => $data->tgl_order,
    //                     'qtyOrder' => $data->qtyOrder,
    //                 ]);
    //             }
    //         }

    //         $temporaryData = $this->connFifth->table('oc')->paginate(10000);
    //         return $temporaryData;
    //         // return $queryBase;
    //     } catch (\Throwable $th) {
    //         return $th->getMessage();
    //     }
    // }



}
