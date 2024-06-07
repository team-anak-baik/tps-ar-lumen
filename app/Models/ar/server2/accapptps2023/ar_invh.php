<?php

namespace App\Models\ar\server2\accapptps2023;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ar_invh extends Model
{
    protected $connection = 'connection_fourth';
    protected $table = 'accapptps2023.ar_invh';
    protected $connFourth, $connFifth;

    public function __construct()
    {
        $this->connFourth = DB::connection('connection_fourth');
        $this->connFifth = DB::connection('connection_fifth');
    }

    public function getData($startDate, $endDate, $userid)
    // public function getData()
    {
        try {
            if (!Schema::connection('connection_fifth')->hasTable('tempt_invc' . $userid)) {
                // Truncate the table if it exists
                Schema::connection('connection_fifth')->create('tempt_invc' . $userid, function (Blueprint $table) {
                    $table->increments('id');
                    $table->string('docnum')->nullable();
                    $table->dateTime('docdate')->nullable();
                    $table->string('doctotalamt')->nullable();
                    $table->string('orderno')->nullable();
                });
            }
            $queryBase = $this->connFourth->table('accapptps2023.ar_invh AS invc')
                ->select('invc.docnum', 'invc.docdate', 'invc.doctotalamt', 'invc.orderno')
                ->where('invc.orderno', '!=', '')
                ->whereBetween('invc.docdate', [$startDate, $endDate])
                ->orderBy('invc.docdate', 'DESC')
                ->get();

            foreach ($queryBase as $data) {
                $cekData = $this->connFifth->table('tempt_invc' . $userid)->where('docnum', $data->docnum)->first();
                if (empty($cekData)) {
                    $this->connFifth->table('tempt_invc' . $userid)->insert([
                        'docnum' => $data->docnum,
                        'docdate' => $data->docdate,
                        'doctotalamt' => $data->doctotalamt,
                        'orderno' => $data->orderno,
                    ]);
                }
            }

            $temporaryData = $this->connFifth->table('tempt_invc' . $userid)->paginate(10000);
            return $temporaryData;
            // return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
