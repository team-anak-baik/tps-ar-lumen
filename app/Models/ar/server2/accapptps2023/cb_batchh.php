<?php

namespace App\Models\ar\server2\accapptps2023;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class cb_batchh extends Model
{
    protected $connection = 'connection_fourth';
    protected $table = 'accapptps2023.cb_batchh';
    protected $connFourth, $connFifth;

    public function __construct()
    {
        $this->connFourth = DB::connection('connection_fourth');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    {
        try {
            if ($startDate && $endDate) {
                if (!Schema::connection('connection_fifth')->hasTable('tempt_rv' . $userid)) {
                    // Truncate the table if it exists
                    Schema::connection('connection_fifth')->create('tempt_rv' . $userid, function (Blueprint $table) {
                        $table->increments('id');
                        $table->string('no_rv')->nullable();
                        $table->date('rv_date')->nullable();
                        $table->integer('totamount')->nullable();
                        $table->string('docno')->nullable();
                    });
                }
                $queryBase = $this->connFourth->table($this->connFourth->raw('(
                SELECT cbsd.batchno cbsd_batchno, cbh.batchno, cbsd.docno, cbsd.entryno cbsd_entryno, cbh.entryno, cbh.reference no_rv, DATE_FORMAT(STR_TO_DATE(SUBSTRING(cbh.dscription, 1, 8), "%d%m%Y"), "%Y-%m-%d") AS rv_date, cbh.totamount FROM accapptps2023.cb_batchh cbh
                INNER JOIN accapptps2023.cb_batchsd cbsd ON cbh.batchno=cbsd.batchno
                WHERE cbh.entrytype= "R" AND cbh.misccode != "" AND cbsd.entryno=cbh.entryno) AS rv'))
                    ->select('rv.no_rv', 'rv.rv_date', 'rv.totamount', 'rv.docno')
                    ->whereBetween('rv.rv_date', [$startDate, $endDate])
                    ->orderBy('rv.rv_date', 'DESC')
                    ->get();

                foreach ($queryBase as $data) {
                    $formattedDate = date('Y-m-d', strtotime($data->rv_date));
                    $cekData = $this->connFifth->table('tempt_rv' . $userid)->where('no_rv', $data->no_rv)->first();
                    if (empty($cekData)) {
                        $this->connFifth->table('tempt_rv' . $userid)->insert([
                            'no_rv' => $data->no_rv,
                            'rv_date' => $formattedDate,
                            'totamount' => $data->totamount,
                            'docno' => $data->docno,
                        ]);
                    }
                }

                $temporaryData = $this->connFifth->table('tempt_rv' . $userid)->paginate(10000);
                return $temporaryData;
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
