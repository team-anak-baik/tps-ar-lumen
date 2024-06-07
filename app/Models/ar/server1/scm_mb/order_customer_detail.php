<?php

namespace App\Models\ar\server1\scm_mb;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class order_customer_detail extends Model
{
    protected $connection = 'connection_first';
    protected $table = 'scm_mb.order_customer_detail';

    public function __construct()
    {
        $this->connFirst = DB::connection('connection_first');
    }

    public function getData()
    {
        try {
            $queryBase = $this->connFirst->table($this->table)
                ->whereBetween('uploaded_at', ['2024-05-01 00:00:00', '2024-05-17 00:00:00'])
                ->paginate(10000);
            return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
