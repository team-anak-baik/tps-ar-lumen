<?php

namespace App\Models\ar\server1\wms_tps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class delivery extends Model
{
    protected $connection = 'connection_second';
    protected $table = 'wms_tps.delivery';
    protected $connSecond, $connFifth;

    public function __construct()
    {
        $this->connSecond = DB::connection('connection_second');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    {
        try {
            if (!Schema::connection('connection_fifth')->hasTable('tempt_dotps_wms' . $userid)) {
                // Truncate the table if it exists
                Schema::connection('connection_fifth')->create('tempt_dotps_wms' . $userid, function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('baseEntry')->nullable();
                    $table->integer('dlvrNo')->nullable();
                    $table->date('dlvrDate')->nullable();
                    $table->integer('dlvrqty')->nullable();
                });
            }
            $queryBase = $this->connSecond->table($this->connSecond->raw('(
                SELECT o1.baseEntry ,o1.docEntry,o.docNum dlvrNo,o.docDate dlvrDate, SUM(o1.quantity) dlvrqty FROM wms_tps.delivery o
                INNER JOIN wms_tps.deliverydetail1 o1 ON o.docEntry=o1.docEntry
                GROUP BY o1.baseEntry,o1.docEntry) AS dotps_wms'))
                ->select('dotps_wms.baseEntry', 'dotps_wms.dlvrNo', 'dotps_wms.dlvrDate', 'dotps_wms.dlvrqty')
                ->whereBetween('dotps_wms.dlvrDate', [$startDate, $endDate])
                ->orderBy('dotps_wms.dlvrDate', 'DESC')
                ->get();

            foreach ($queryBase as $data) {
                $cekData = $this->connFifth->table('tempt_dotps_wms' . $userid)->where('dlvrNo', $data->dlvrNo)->first();
                if (empty($cekData)) {
                    $this->connFifth->table('tempt_dotps_wms' . $userid)->insert([
                        'baseEntry' => $data->baseEntry,
                        'dlvrNo' => $data->dlvrNo,
                        'dlvrDate' => $data->dlvrDate,
                        'dlvrqty' => $data->dlvrqty,
                    ]);
                }
            }

            $temporaryData = $this->connFifth->table('tempt_dotps_wms' . $userid)->paginate(10000);
            return $temporaryData;
            // return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
