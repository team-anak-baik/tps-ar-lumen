<?php

namespace App\Models\ar\server2\accapptps2023;

use Illuminate\Database\Eloquent\Model;

class customers extends Model
{
    protected $connection = "connection_fourth";
    protected $table = "accapptps2023.ar_customer";

    public function invoices()
    {
        return $this->hasMany(ar_invobl::class, 'custmrcode', 'custmrcode');
    }
}
