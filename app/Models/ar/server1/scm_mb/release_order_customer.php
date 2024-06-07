<?php

namespace App\Models\ar\server1\scm_mb;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Date;
use Throwable;
use Carbon\Carbon;

class release_order_customer extends Model
{
    protected $connection = 'connection_first';
    protected $table = 'scm_mb.release_order_customer';
    protected $connFirst, $connFifth;

    public function __construct()
    {
        $this->connFirst = DB::connection('connection_first');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    {
        try {
            // Check if the table exists
            if (!Schema::connection('connection_fifth')->hasTable('tempt_roc' . $userid)) {
                // Truncate the table if it exists
                Schema::connection('connection_fifth')->create('tempt_roc' . $userid, function (Blueprint $table) {
                    $table->increments('id');
                    $table->integer('baseId')->nullable();
                    $table->integer('releaseId')->nullable();
                    $table->string('release_no', 255)->nullable();
                    $table->date('tgl_release')->nullable();
                    $table->integer('releaseQty')->nullable();
                });
            }
            $queryBase = $this->connFirst->table($this->connFirst->raw('(
                SELECT oc.uploaded_at, oc.baseId,oc1.releaseId,oc.baseDocEntry,oc.release_no,oc.tgl_release, SUM(oc1.releaseQty) releaseQty FROM scm_mb.release_order_customer oc
                INNER JOIN scm_mb.release_order_customer_detail oc1 ON oc.id=oc1.releaseId
                WHERE oc.company=4 AND oc.tipe="SO"
                GROUP BY oc.baseId,oc1.releaseId,oc.baseDocEntry,oc.release_no) AS roc'))
                ->select('roc.baseId', 'roc.releaseId', 'roc.release_no', 'roc.tgl_release', 'roc.releaseQty')
                ->whereBetween('roc.tgl_release', [$startDate, $endDate])
                ->orderBy('roc.tgl_release', 'DESC')
                ->get();

            foreach ($queryBase as $data) {
                $cekData = $this->connFifth->table('tempt_roc' . $userid)->where('release_no', $data->release_no)->first();
                if (empty($cekData)) {
                    $this->connFifth->table('tempt_roc' . $userid)->insert([
                        'baseId' => $data->baseId,
                        'releaseId' => $data->releaseId,
                        'release_no' => $data->release_no,
                        'tgl_release' => $data->tgl_release,
                        'releaseQty' => $data->releaseQty,
                    ]);
                }
            }

            $temporaryData = $this->connFifth->table('tempt_roc' . $userid)->paginate(10000);
            return $temporaryData;
            // return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
