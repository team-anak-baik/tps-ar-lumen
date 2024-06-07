<?php

namespace App\Models\ar\lokal\distributor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

class monitoring extends Model
{
    protected $connection = 'connection_fifth';
    protected $connFift;

    public function __construct()
    {
        $this->connFift = DB::connection('connection_fifth');
    }

    public function getData($step = null, $type = 'lengkap', $month, $year, $userid, $perPage = null, $startDate = null, $endDate = null)
    {
        try {

            if (is_null($startDate)) {
                $startDate = date('Y-m-01');
            }
            if (is_null($endDate)) {
                $endDate = date('Y-m-t');
            }

            $tempt_oc = 'tempt_oc' . $userid;
            $tempt_roc = 'tempt_roc' . $userid;
            $tempt_so = 'tempt_so' . $userid;
            $tempt_dotps = 'tempt_dotps' . $userid;
            $tempt_so_wms = 'tempt_so_wms' . $userid;
            $tempt_dotps_wms = 'tempt_dotps_wms' . $userid;
            $tempt_invc = 'tempt_invc' . $userid;
            $tempt_rv = 'tempt_rv' . $userid;

            $queryBase = $this->connFift->table($tempt_oc)
                ->leftJoin($tempt_roc, "$tempt_oc.orderId", '=', "$tempt_roc.baseId")
                ->leftJoin("$tempt_so", "$tempt_roc.releaseId", '=', "$tempt_so.releaseId")
                ->leftJoin("$tempt_dotps", "$tempt_so.docEntry", '=', "$tempt_dotps.baseEntry")
                ->leftJoin("$tempt_so_wms", "$tempt_dotps.docEntry", '=', "$tempt_so_wms.baseEntry")
                ->leftJoin("$tempt_dotps_wms", "$tempt_so_wms.docEntry", '=', "$tempt_dotps_wms.baseEntry")
                ->leftJoin("$tempt_invc", "$tempt_dotps_wms.dlvrNo", '=', "$tempt_invc.orderno")

                ->select(
                    "$tempt_oc.custmrCode AS kodeCustomer",
                    "$tempt_oc.custmrName AS namaCustomer",
                    "$tempt_oc.no_order AS nomorPO",
                    "$tempt_oc.tgl_order AS tanggalPO",
                    "$tempt_oc.qtyOrder AS unitPO",
                    "$tempt_dotps_wms.dlvrNo AS nomorDO",
                    "$tempt_dotps_wms.dlvrDate AS tanggalDO",
                    "$tempt_dotps_wms.dlvrqty AS unitDO",
                    "$tempt_invc.docnum",
                    "$tempt_invc.docdate",
                    "$tempt_invc.doctotalamt"
                )
                ->selectRaw("(SELECT ($tempt_invc.doctotalamt -sum(totamount)) FROM $tempt_rv WHERE docno=$tempt_invc.docnum) as aramt")
                ->selectRaw("(SELECT count(*) FROM $tempt_rv WHERE $tempt_rv.docno=$tempt_invc.docnum) as receipt");
            // ->selectRaw("(SELECT SUM($tempt_invc.doctotalamt) FROM $tempt_invc) as totinvc");

            if ($startDate && $endDate) {
                if ($step == 'inv') {
                    $queryBase = $queryBase->where("$tempt_invc.docnum", '!=', '')->havingRaw("(SELECT count(*) FROM $tempt_rv WHERE $tempt_rv.docno=$tempt_invc.docnum) = 0")->whereBetween("$tempt_invc.docdate", [$startDate, $endDate])->orderBy("$tempt_invc.docdate", 'DESC');
                } elseif ($step == 'rv') {
                    $queryBase = $queryBase->whereRaw("(SELECT count(*) FROM $tempt_rv WHERE $tempt_rv.docno=$tempt_invc.docnum) != 0")->whereBetween("$tempt_invc.docdate", [$startDate, $endDate])->orderBy("$tempt_invc.docdate", 'DESC');
                } elseif ($step == 'po') {
                    $queryBase = $queryBase->where("$tempt_oc.no_order", '!=', '')->whereNull("$tempt_dotps_wms.dlvrNo")->whereBetween("$tempt_oc.tgl_order", [$startDate, $endDate])->orderBy("$tempt_oc.tgl_order", 'DESC');
                } elseif ($step == 'do') {
                    $queryBase = $queryBase->where("$tempt_dotps_wms.dlvrNo", '!=', '')->whereNull("$tempt_invc.docnum")->whereBetween("$tempt_dotps_wms.dlvrDate", [$startDate, $endDate])->orderBy("$tempt_dotps_wms.dlvrDate", 'DESC');
                } elseif ($step == 'all') {
                    $queryBase = $queryBase->whereBetween("$tempt_oc.tgl_order", [$startDate, $endDate])->orderBy("$tempt_oc.tgl_order", 'DESC');
                }
            }

            if ($type == 'lengkap') {
                $queryBase = $queryBase->paginate($perPage);
            }
            // elseif ($type == 'po') {
            //     $queryBase = $queryBase->whereMonth("$tempt_oc.tgl_order", $month)->whereYear("$tempt_oc.tgl_order", $year)->get();
            // } elseif ($type == 'do') {
            //     $queryBase = $queryBase->whereMonth("$tempt_dotps_wms.dlvrDate", $month)->whereYear("$tempt_dotps_wms.dlvrDate", $year)->get();
            // } elseif ($type == 'inv') {
            //     $queryBase = $queryBase->whereMonth("$tempt_invc.docdate", $month)->whereYear("$tempt_invc.docdate", $year)->get();
            // }
            // // elseif ($type == 'rv') {
            // //     $queryBase = $queryBase->whereMonth("$tempt_rv.rv_date", $month)->whereYear("$tempt_rv.rv_date", $year)->get();
            // // } 
            // elseif ($type == 'invamt') {
            //     $queryBase = $queryBase
            //         // ->selectRaw("SUM(DISTINCT $tempt_invc.doctotalamt) AS doctotalamt")
            //         // ->selectRaw("(SELECT SUM($tempt_invc.doctotalamt) FROM $tempt_invc WHERE $tempt_invc.docdate = $month) as totinvc")
            //         ->selectRaw("(SELECT SUM(doctotalamt) FROM $tempt_invc WHERE MONTH ($tempt_invc.docdate) =  $month)  as totinvc")
            //         ->whereMonth("$tempt_invc.docdate", $month)
            //         ->whereYear("$tempt_invc.docdate", Date::now()->year)->get();
            //     $total = null;
            //     foreach ($queryBase as $key) {
            //         foreach ($queryBase as $key) {
            //             $total += $key->doctotalamt;
            //         }
            //         $queryBase = $total;
            //     }
            // }
            // elseif ($type == 'rvamt') {
            //     $queryBase = $queryBase
            //         ->select("$tempt_rv.totamount")
            //         ->whereMonth("$tempt_rv.rv_date", $month)
            //         ->whereYear("$tempt_rv.rv_date", Date::now()->year)->get();
            //     $total = null;
            //     foreach ($queryBase as $key) {
            //         $total += $key->totamount;
            //     }
            //     $queryBase = $total;
            // }

            return $queryBase;
        } catch (\Throwable $th) {
            return [
                'error' => true,
                'message' => $th->getMessage()
            ];
        }
    }

    public function countAll($type, $userid, $month = null, $year = null)
    {
        try {
            if (!$month) {
                $month = date("n");
            }
            if (!$year) {
                $year = Date::now()->year;
            }

            $tempt_oc = 'tempt_oc' . $userid;
            $tempt_roc = 'tempt_roc' . $userid;
            $tempt_so = 'tempt_so' . $userid;
            $tempt_dotps = 'tempt_dotps' . $userid;
            $tempt_so_wms = 'tempt_so_wms' . $userid;
            $tempt_dotps_wms = 'tempt_dotps_wms' . $userid;
            $tempt_invc = 'tempt_invc' . $userid;
            $tempt_rv = 'tempt_rv' . $userid;

            $queryBase = $this->connFift->table($tempt_oc)
                ->leftJoin($tempt_roc, "$tempt_oc.orderId", '=', "$tempt_roc.baseId")
                ->leftJoin("$tempt_so", "$tempt_roc.releaseId", '=', "$tempt_so.releaseId")
                ->leftJoin("$tempt_dotps", "$tempt_so.docEntry", '=', "$tempt_dotps.baseEntry")
                ->leftJoin("$tempt_so_wms", "$tempt_dotps.docEntry", '=', "$tempt_so_wms.baseEntry")
                ->leftJoin("$tempt_dotps_wms", "$tempt_so_wms.docEntry", '=', "$tempt_dotps_wms.baseEntry")
                ->leftJoin("$tempt_invc", "$tempt_dotps_wms.dlvrNo", '=', "$tempt_invc.orderno")
                ->leftJoin("$tempt_rv", "$tempt_invc.docnum", '=', "$tempt_rv.docno");

            if ($type == 'po') {
                $queryBase = $queryBase->whereMonth("$tempt_oc.tgl_order", $month)->whereYear("$tempt_oc.tgl_order", $year)->orderBy("$tempt_oc.tgl_order")->get();
            } elseif ($type == 'do') {
                $queryBase = $queryBase->whereMonth("$tempt_dotps_wms.dlvrDate", $month)->whereYear("$tempt_dotps_wms.dlvrDate", $year)->orderBy("$tempt_dotps_wms.dlvrDate")->get();
            } elseif ($type == 'inv') {
                $queryBase = $queryBase->whereMonth("$tempt_invc.docdate", $month)->whereYear("$tempt_invc.docdate", $year)->orderBy("$tempt_invc.docdate")->get();
            } elseif ($type == 'rv') {
                $queryBase = $queryBase->whereMonth("$tempt_rv.rv_date", $month)->whereYear("$tempt_rv.rv_date", $year)->orderBy("$tempt_rv.rv_date")->get();
            } elseif ($type == 'invamt') {
                $queryBase = $queryBase->selectRaw("SUM(DISTINCT $tempt_invc.doctotalamt) AS invamt")->whereMonth("$tempt_invc.docdate", $month)->whereYear("$tempt_invc.docdate", $year)->get();
            } elseif ($type == 'rvamt') {
                $queryBase = $queryBase->selectRaw("SUM(DISTINCT $tempt_rv.totamount) AS rvamt")->whereMonth("$tempt_rv.rv_date", $month)->whereYear("$tempt_rv.rv_date", $year)->get();
            }

            // $po = $this->getData($step, 'po', $month, $year, $userid)->count();
            // $do = $this->getData($step, 'do', $month, $year, $userid)->count();
            // $inv = $this->getData($step, 'inv', $month, $year, $userid)->count();
            // $rv = $this->getData($step, 'rv', $month, $year, $userid)->count();
            // $invamt = $this->getData($step, 'invamt', $month, $year, $userid);
            // $rvamt = $this->getData($step, 'rvamt', $month, $year, $userid);
            // $data = [
            //     'po' => $po,
            //     'do' => $do,
            //     'inv' => $inv,
            //     // 'rv' => $rv,
            //     'invamt' => $invamt,
            //     // 'rvamt' => $rvamt,
            // ];

            // $poData = $this->getData($step, 'po', $month, $year, $userid);
            // $doData = $this->getData($step, 'do', $month, $year, $userid);
            // $invData = $this->getData($step, 'inv', $month, $year, $userid);
            // $rvData = $this->getData($step, 'rv', $month, $year, $userid);
            // $invamtData = $this->getData($step, 'invamt', $month, $year, $userid);
            // // $rvamtData = $this->getData($step, 'rvamt', $month, $year, $userid);

            // // Check for errors
            // $po = isset($poData['error']) ? 0 : $poData->count();
            // $do = isset($doData['error']) ? 0 : $doData->count();
            // $inv = isset($invData['error']) ? 0 : $invData->count();
            // $rv = isset($rvData['error']) ? 0 : $rvData->count();
            // $invamt = isset($invamtData['error']) ? 0 : $invamtData;
            // // $rvamt = isset($rvamtData['error']) ? 0 : $rvamtData;

            // $data = [
            //     'po' => $po,
            //     'do' => $do,
            //     'inv' => $inv,
            //     'rv' => $rv,
            //     'invamt' => $invamt,
            //     // 'rvamt' => $rvamt,
            // ];

            // return $data;
            return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function countData($month = null, $year = null, $userid)
    {
        $po = $this->countAll('po', $month, $year, $userid)->count();
        $do = $this->countAll('do', $month, $year, $userid)->count();
        $inv = $this->countAll('inv', $month, $year, $userid)->count();
        $rv = $this->countAll('rv', $month, $year, $userid)->count();
        $invamt = $this->countAll('invamt', $month, $year, $userid);
        $rvamt = $this->countAll('rvamt', $month, $year, $userid);
        $data = [
            'po' => $po,
            'do' => $do,
            'inv' => $inv,
            'rv' => $rv,
            'invamt' => $invamt,
            'rvamt' => $rvamt,
        ];
        return $data;
    }

    public function getReceipt($code = null, $userid)
    {
        $tempt_invc = 'tempt_invc' . $userid;
        $tempt_rv = 'tempt_rv' . $userid;
        $totalRv = $this->connFift->table($tempt_invc)
            ->leftJoin("$tempt_rv", "$tempt_invc.docnum", '=', "$tempt_rv.docno")
            ->where("$tempt_invc.docnum", $code)
            ->count();
        $voucher = $this->connFift->table($tempt_invc)
            ->select("$tempt_rv.no_rv", "$tempt_rv.rv_date", "$tempt_rv.totamount")
            ->leftJoin("$tempt_rv", "$tempt_invc.docnum", '=', "$tempt_rv.docno")
            ->where("$tempt_invc.docnum", $code)
            ->get();
        $data = [
            'invoice' => $code,
            'totrv' => $totalRv,
            'voucher' => $voucher,
        ];
        return $data;
    }


    public function searchData($keyword, $type = 'lengkap', $userid, $perPage = 2000)
    {
        try {
            $tempt_oc = 'tempt_oc' . $userid;
            $tempt_roc = 'tempt_roc' . $userid;
            $tempt_so = 'tempt_so' . $userid;
            $tempt_dotps = 'tempt_dotps' . $userid;
            $tempt_so_wms = 'tempt_so_wms' . $userid;
            $tempt_dotps_wms = 'tempt_dotps_wms' . $userid;
            $tempt_invc = 'tempt_invc' . $userid;
            $tempt_rv = 'tempt_rv' . $userid;

            $queryBase = $this->connFift->table($tempt_oc)
                ->leftJoin($tempt_roc, "$tempt_oc.orderId", '=', "$tempt_roc.baseId")
                ->leftJoin("$tempt_so", "$tempt_roc.releaseId", '=', "$tempt_so.releaseId")
                ->leftJoin("$tempt_dotps", "$tempt_so.docEntry", '=', "$tempt_dotps.baseEntry")
                ->leftJoin("$tempt_so_wms", "$tempt_dotps.docEntry", '=', "$tempt_so_wms.baseEntry")
                ->leftJoin("$tempt_dotps_wms", "$tempt_so_wms.docEntry", '=', "$tempt_dotps_wms.baseEntry")
                ->leftJoin("$tempt_invc", "$tempt_dotps_wms.dlvrNo", '=', "$tempt_invc.orderno")

                ->select(
                    "$tempt_oc.custmrCode AS kodeCustomer",
                    "$tempt_oc.custmrName AS namaCustomer",
                    "$tempt_oc.no_order AS nomorPO",
                    "$tempt_oc.tgl_order AS tanggalPO",
                    "$tempt_oc.qtyOrder AS unitPO",
                    "$tempt_dotps_wms.dlvrNo AS nomorDO",
                    "$tempt_dotps_wms.dlvrDate AS tanggalDO",
                    "$tempt_dotps_wms.dlvrqty AS unitDO",
                    "$tempt_invc.docnum",
                    "$tempt_invc.docdate",
                    "$tempt_invc.doctotalamt"
                )
                ->selectRaw("(SELECT ($tempt_invc.doctotalamt -sum(totamount)) FROM tempt_rv1 WHERE docno=tempt_invc1.docnum) as aramt")
                ->selectRaw("(SELECT count(*) FROM $tempt_rv WHERE $tempt_rv.docno=$tempt_invc.docnum) as receipt")
                // ->selectRaw("count($tempt_rv.no_rv) AS total_rv")
                // ->where('invc.docnum', '!=', '')
                // ->orderBy('invc.docnum', 'DESC')
                // ->whereBetween("$tempt_oc.tgl_order", [$startDate, $endDate])
                // ->groupBy("$tempt_invc.docnum")
                ->where("$tempt_oc.no_order", 'LIKE', "%$keyword%")
                ->orWhere("$tempt_invc.docnum", 'LIKE', "%$keyword%")
                ->orWhere("$tempt_dotps_wms.dlvrNo", 'LIKE', "%$keyword%")
                ->orWhere("$tempt_oc.custmrName", 'LIKE', "%$keyword%")
                ->orderBy("$tempt_oc.tgl_order", 'DESC');

            if ($type == 'lengkap') {
                $queryBase = $queryBase->paginate($perPage);
                // foreach ($queryBase as $key) {
                //     $amount = $this->connFift->table($tempt_invc)
                //         ->select($this->connFift->raw("SUM($tempt_rv.totamount) AS totamt"))
                //         ->leftJoin("$tempt_rv", "$tempt_invc.docnum", '=', "$tempt_rv.docno")
                //         ->where("$tempt_invc.docnum", $key->docnum)
                //         ->first();
                //     if ($amount) {
                //         $key->amountrv = $amount->totamt;
                //     } else {
                //         $key->amountrv = null;
                //     }
                // }
                // foreach ($queryBase as $key) {
                //     $amount = $this->connFift->table($tempt_invc)
                //         ->select($this->connFift->raw("count($tempt_invc.docnum) AS total_rv"))
                //         ->leftJoin("$tempt_rv", "$tempt_invc.docnum", '=', "$tempt_rv.docno")
                //         ->where("$tempt_invc.docnum", $key->docnum)
                //         ->first();
                //     if ($amount) {
                //         $key->totrv = $amount->total_rv;
                //     } else {
                //         $key->totrv = null;
                //     }
                // }
            }

            return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
