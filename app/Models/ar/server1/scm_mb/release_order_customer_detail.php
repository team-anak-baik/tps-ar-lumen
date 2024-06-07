<?php

namespace App\Models\ar\server1\scm_mb;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Throwable;
use Carbon\Carbon;

class release_order_customer extends Model
{
    protected $connection = 'connection_first';
    protected $table = 'scm_mb.release_order_customer';
    protected $connFirst, $connSecond;

    public function __construct()
    {
        $this->connFirst = DB::connection('connection_first');
    }

    public function getData()
    {
        try {
            $queryBase = $this->connFirst->table($this->connFirst->raw('(
                SELECT oc.baseId,oc1.releaseId,oc.baseDocEntry,oc.release_no,oc.tgl_release, SUM(oc1.releaseQty) releaseQty FROM scm_mb.release_order_customer oc
                INNER JOIN scm_mb.release_order_customer_detail oc1 ON oc.id=oc1.releaseId
                WHERE oc.company=4 AND oc.tipe="SO"
                GROUP BY oc.baseId,oc1.releaseId,oc.baseDocEntry,oc.release_no) AS roc'))
                ->select('roc.release_no', 'roc.tgl_release', 'oc.qtyOrder', 'roc.releaseQty')
                ->whereBetween('roc.uploaded_at', ['2024-04-01 00:00:00', '2024-04-30 00:00:00'])
                ->orderBy('roc.tgl_order', 'DESC')
                ->get();

            // foreach ($queryBase as $data) {
            //     // Simpan $data ke dalam tabel temporary
            //     // Misalnya:
            //     $cekData = $this->connFifth->table('rpo')->where('no_order', $data->no_order)->first();
            //     if (empty($cekData)) {
            //         $this->connFifth->table('rpo')->insert([
            //             'custmrCode' => $data->custmrCode,
            //             'custmrName' => $data->custmrName,
            //             'no_order' => $data->no_order,
            //             'tgl_order' => $data->tgl_order,
            //         ]);
            //     }
            // }

            // $temporaryData = $this->connFifth->table('po')->paginate(10000);
            // return $temporaryData;
            return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
