<?php

namespace App\Models\ar\server1\tps;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class order extends Model
{
    protected $connection = 'connection_second';
    protected $table = 'tps.order';
    protected $connSecond, $connFifth;

    public function __construct()
    {
        $this->connSecond = DB::connection('connection_second');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    {
        try {
            if (!Schema::connection('connection_fifth')->hasTable('tempt_so' . $userid)) {
                // Truncate the table if it exists
                Schema::connection('connection_fifth')->create('tempt_so' . $userid, function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('releaseId')->nullable();
                    $table->integer('docEntry')->nullable();
                    $table->integer('orderNo')->nullable();
                    $table->date('orderDate')->nullable();
                    $table->integer('orderqty')->nullable();
                });
            }

            $queryBase = $this->connSecond->table($this->connSecond->raw('(
                SELECT  o1.baseEntry releaseId,o1.docEntry,o.docNum orderNo,o.docDate orderDate, SUM(o1.quantity) orderqty FROM tps.order o
                INNER JOIN tps.orderdetail1 o1 ON o.docEntry=o1.docEntry
                WHERE o1.baseType=88
                GROUP BY o1.baseEntry,o1.docEntry) AS so'))
                ->select('so.releaseId', 'so.docEntry', 'so.orderNo', 'so.orderDate', 'so.orderqty')
                // ->whereBetween('so.orderDate', ['2024-01-01', '2024-05-17'])
                ->whereBetween('so.orderDate', [$startDate, $endDate])
                ->orderBy('so.orderDate', 'DESC')
                ->get();

            foreach ($queryBase as $data) {
                $cekData = $this->connFifth->table('tempt_so' . $userid)->where('orderNo', $data->orderNo)->first();
                if (empty($cekData)) {
                    $this->connFifth->table('tempt_so' . $userid)->insert([
                        'releaseId' => $data->releaseId,
                        'docEntry' => $data->docEntry,
                        'orderNo' => $data->orderNo,
                        'orderDate' => $data->orderDate,
                        'orderqty' => $data->orderqty,
                    ]);
                }
            }

            $temporaryData = $this->connFifth->table('tempt_so' . $userid)->paginate(10000);
            return $temporaryData;
            // return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
