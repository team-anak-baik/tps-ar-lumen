<?php

namespace App\Models\ar\server2\accapptps2023;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;
// use Throwable;

class ar_invobl extends Model
{
    protected $connection = 'connection_fourth';
    protected $table = 'accapptps2023.ar_invobl';
    protected $connFourth;

    public function __construct()
    {
        $this->connFourth = DB::connection('connection_fourth');
    }

    public function customers()
    {
        return $this->belongsTo(customers::class, 'custmrcode', 'custmrcode');
    }

    public function getAging($month)
    {
        try {
            $today = Date::now();
            $currentYear = (int) $today->format('Y');

            $results = $this->selectRaw('
                SUM(doctotalamtr) as totalBill,
                SUM(CASE WHEN MONTH(docduedate) = ? AND YEAR(docduedate) = ? THEN doctotalamtr ELSE 0 END) as totalBillByMonth,
                SUM(CASE WHEN MONTH(docduedate) = ? AND YEAR(docduedate) = ? THEN doctotalamtr ELSE 0 END) as totalNotPaidByMonth,
                SUM(CASE WHEN MONTH(docduedate) = ? AND YEAR(docduedate) = ? THEN rcptotalamt ELSE 0 END) as totalPaidByMonth,
                SUM(CASE WHEN swpaid = 0 AND docduedate < ? THEN doctotalamtr ELSE 0 END) as totalBillOverDue
            ', [$month, $currentYear, $month, $currentYear, $month, $currentYear, $today])
                ->where('swpaid', 0)
                ->first();

            $monthlyDebts = $this->selectRaw('
                mop AS month,
                SUM(DISTINCT doctotalamt) as totalDebt,
                SUM(DISTINCT rcptotalamt) as totalPaid
            ')
                ->where('yop', $currentYear)
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $customers = customers::whereHas('invoices', function ($query) use ($currentYear) {
                $query->where('swpaid', 0)
                    ->whereYear('docduedate', $currentYear);
            })
                ->with(['invoices' => function ($query) use ($currentYear) {
                    $query->where('swpaid', 0)
                        ->whereYear('docduedate', $currentYear);
                }])
                ->withSum(['invoices' => function ($query) use ($currentYear) {
                    $query->where('swpaid', 0)
                        ->whereYear('docduedate', $currentYear);
                }], 'doctotalamtr')
                ->get();

            $data = [
                "totalBill" => $results->totalBill,
                "totalBillByMonth" => $results->totalBillByMonth,
                "totalPaidByMonth" => $results->totalPaidByMonth,
                "totalNotPaidByMonth" => $results->totalNotPaidByMonth,
                "totalBillOverDue" => $results->totalBillOverDue,
                "monthlyDebts" => $monthlyDebts,
                "customers" => $customers,
            ];

            return $data;
        } catch (\Throwable $th) {
            return false;
        }
    }

    // public function getAgingSummary($cutOffDoc, $cutOffDue)
    // {
    //     try {
    //         $data = customers::with(['invoices' => function ($query) use ($cutOffDoc, $cutOffDue) {
    //             $query->select('custmrcode', 'docdate', 'docduedate', 'doctotalamtr')
    //                 ->where('swpaid', 0)
    //                 ->where(function ($q) use ($cutOffDoc, $cutOffDue) {
    //                     $q->where('docdate', '<=', $cutOffDoc)
    //                         ->orWhere('docduedate', '<=', $cutOffDue);
    //                 });
    //         }])
    //             ->whereHas('invoices', function ($query) use ($cutOffDoc, $cutOffDue) {
    //                 $query->where('swpaid', 0)
    //                     ->where(function ($q) use ($cutOffDoc, $cutOffDue) {
    //                         $q->where('docdate', '<=', $cutOffDoc)
    //                             ->orWhere('docduedate', '<=', $cutOffDue);
    //                     });
    //             })
    //             ->get()
    //             ->map(function ($customer) use ($cutOffDue) {
    //                 $invoices = $customer->invoices->groupBy(function ($invoice) use ($cutOffDue) {
    //                     $dueDate = strtotime($invoice->docduedate);
    //                     $cutoffDate = strtotime($cutOffDue);
    //                     if ($dueDate <= $cutoffDate) {
    //                         if ($dueDate >= strtotime($cutOffDue . '-90 days')) {
    //                             if ($dueDate >= strtotime($cutOffDue . '-60 days')) {
    //                                 if ($dueDate >= strtotime($cutOffDue . '-30 days')) {
    //                                     return 'invoices1';
    //                                 } else {
    //                                     return 'invoices2';
    //                                 }
    //                             } else {
    //                                 return 'invoices3';
    //                             }
    //                         } else {
    //                             return 'invoices4';
    //                         }
    //                     } else {
    //                         $dueMonth = date('Y-m', $dueDate);
    //                         $cutOffMonth = date('Y-m', strtotime($cutOffDue));
    //                         if ($dueMonth == $cutOffMonth) {
    //                             return 'invoices5';
    //                         }
    //                         return 0;
    //                     }
    //                 });

    //                 $totalInvoice1 = $invoices->has('invoices1') ? $invoices['invoices1']->sum('doctotalamtr') : 0;
    //                 $totalInvoice2 = $invoices->has('invoices2') ? $invoices['invoices2']->sum('doctotalamtr') : 0;
    //                 $totalInvoice3 = $invoices->has('invoices3') ? $invoices['invoices3']->sum('doctotalamtr') : 0;
    //                 $totalInvoice4 = $invoices->has('invoices4') ? $invoices['invoices4']->sum('doctotalamtr') : 0;
    //                 $totalInvoice5 = $invoices->has('invoices5') ? $invoices['invoices5']->sum('doctotalamtr') : 0;

    //                 $totalCustomer = $totalInvoice1 + $totalInvoice2 + $totalInvoice3 + $totalInvoice4 + $totalInvoice5;

    //                 return [
    //                     'customer_number' => $customer->custmrcode,
    //                     'customer_name' => $customer->custmrname,
    //                     'totals' => [
    //                         'invoices1' => $totalInvoice1,
    //                         'invoices2' => $totalInvoice2,
    //                         'invoices3' => $totalInvoice3,
    //                         'invoices4' => $totalInvoice4,
    //                         'invoices5' => $totalInvoice5,
    //                         'total_customer' => $totalCustomer
    //                     ]
    //                 ];
    //             });

    //         $totalAllCustomers = [
    //             'invoices1' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices1'];
    //             }),
    //             'invoices2' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices2'];
    //             }),
    //             'invoices3' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices3'];
    //             }),
    //             'invoices4' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices4'];
    //             }),
    //             'invoices5' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices5'];
    //             }),
    //             'total_customer' => $data->sum(function ($item) {
    //                 return $item['totals']['total_customer'];
    //             }),
    //         ];

    //         return ['main' => $data, 'total_all_customers' => $totalAllCustomers, 'summary' => true];
    //     } catch (\Throwable $th) {
    //         return false;
    //     }
    // }

    // public function getAgingDetail($cutOffDoc, $cutOffDue)
    // {
    //     try {
    //         $data = customers::with(['invoices' => function ($query) use ($cutOffDoc, $cutOffDue) {
    //             $query->select('custmrcode', 'docnum', 'docdate', 'docduedate', 'doctotalamtr')
    //                 ->where('swpaid', 0)
    //                 ->where(function ($q) use ($cutOffDoc, $cutOffDue) {
    //                     $q->where('docdate', '<=', $cutOffDoc)
    //                         ->orWhere('docduedate', '<=', $cutOffDue);
    //                 });
    //         }])
    //             ->whereHas('invoices', function ($query) use ($cutOffDoc, $cutOffDue) {
    //                 $query->where('swpaid', 0)
    //                     ->where(function ($q) use ($cutOffDoc, $cutOffDue) {
    //                         $q->where('docdate', '<=', $cutOffDoc)
    //                             ->orWhere('docduedate', '<=', $cutOffDue);
    //                     });
    //             })
    //             ->get()
    //             ->map(function ($customer) use ($cutOffDue) {
    //                 $invoices = $customer->invoices->groupBy(function ($invoice) use ($cutOffDue) {
    //                     $dueDate = strtotime($invoice->docduedate);
    //                     $cutoffDate = strtotime($cutOffDue);
    //                     if ($dueDate <= $cutoffDate) {
    //                         if ($dueDate >= strtotime($cutOffDue . '-90 days')) {
    //                             if ($dueDate >= strtotime($cutOffDue . '-60 days')) {
    //                                 if ($dueDate >= strtotime($cutOffDue . '-30 days')) {
    //                                     return 'invoices1';
    //                                 } else {
    //                                     return 'invoices2';
    //                                 }
    //                             } else {
    //                                 return 'invoices3';
    //                             }
    //                         } else {
    //                             return 'invoices4';
    //                         }
    //                     } else {
    //                         $dueMonth = date('Y-m', $dueDate);
    //                         $cutOffMonth = date('Y-m', strtotime($cutOffDue));
    //                         if ($dueMonth == $cutOffMonth) {
    //                             return 'invoices5';
    //                         }
    //                         return 0;
    //                     }
    //                 })->map(function ($group) {
    //                     return $group->map(function ($invoice) {
    //                         $formattedDocDate = date('d-m-Y', strtotime($invoice->docdate));
    //                         $invoice->fdocdate = $formattedDocDate;
    //                         $formattedDueDate = date('d-m-Y', strtotime($invoice->docduedate));
    //                         $invoice->fdocduedate = $formattedDueDate;
    //                         return $invoice;
    //                     });
    //                 });

    //                 $totalInvoice1 = $invoices->has('invoices1') ? $invoices['invoices1']->sum('doctotalamtr') : 0;
    //                 $totalInvoice2 = $invoices->has('invoices2') ? $invoices['invoices2']->sum('doctotalamtr') : 0;
    //                 $totalInvoice3 = $invoices->has('invoices3') ? $invoices['invoices3']->sum('doctotalamtr') : 0;
    //                 $totalInvoice4 = $invoices->has('invoices4') ? $invoices['invoices4']->sum('doctotalamtr') : 0;
    //                 $totalInvoice5 = $invoices->has('invoices5') ? $invoices['invoices5']->sum('doctotalamtr') : 0;

    //                 $totalCustomer = $totalInvoice1 + $totalInvoice2 + $totalInvoice3 + $totalInvoice4 + $totalInvoice5;

    //                 return [
    //                     'customer_number' => $customer->custmrcode,
    //                     'customer_name' => $customer->custmrcode,
    //                     'invoices' => $invoices,
    //                     'totals' => [
    //                         'invoices1' => $totalInvoice1,
    //                         'invoices2' => $totalInvoice2,
    //                         'invoices3' => $totalInvoice3,
    //                         'invoices4' => $totalInvoice4,
    //                         'invoices5' => $totalInvoice5,
    //                         'total_customer' => $totalCustomer
    //                     ]
    //                 ];
    //             });

    //         $totalAllCustomer = [
    //             'invoices1' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices1'];
    //             }),
    //             'invoices2' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices2'];
    //             }),
    //             'invoices3' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices3'];
    //             }),
    //             'invoices4' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices4'];
    //             }),
    //             'invoices5' => $data->sum(function ($item) {
    //                 return $item['totals']['invoices5'];
    //             }),
    //             'total_customer' => $data->sum(function ($item) {
    //                 return $item['totals']['total_customer'];
    //             }),
    //         ];

    //         return ['main' => $data, 'total_all_customers' => $totalAllCustomer, 'detail' => true];
    //     } catch (\Throwable $th) {
    //         return false;
    //     }
    // }

    public function getAgingAll($cutOffDue)
    {
        try {
            $data = customers::with(['invoices' => function ($query) {
                $query->select('custmrcode', 'docnum', 'docdate', 'docduedate', 'doctotalamtr')
                    ->where('swpaid', 0)
                    ->orderBy('docduedate', 'asc');
            }])->whereHas('invoices', function ($query) {
                $query->where('swpaid', 0);
            })->get();

            $currentTotal = 0;
            $afterTotal = 0;
            $totalData = 0;

            $groupedData = $data->map(function ($vendor) use ($cutOffDue, &$currentTotal, &$afterTotal, &$totalData) {
                $currentData = $vendor->invoices->filter(function ($invoice) use ($cutOffDue) {
                    return Carbon::parse($invoice->docduedate)->lte($cutOffDue);
                });

                $afterData = $vendor->invoices->filter(function ($invoice) use ($cutOffDue) {
                    return Carbon::parse($invoice->docduedate)->gt($cutOffDue);
                });

                $currentTotal += $currentData->sum('doctotalamtr');
                $afterTotal += $afterData->sum('doctotalamtr');
                $totalData += $vendor->invoices->sum('doctotalamtr');

                return [
                    'vendor' => [
                        'id' => $vendor->id,
                        'custmrcode' => $vendor->custmrcode,
                        'custmrname' => $vendor->custmrname,
                        'current' => $currentData->sum('doctotalamtr'),
                        'after' => $afterData->sum('doctotalamtr'),
                        'total' => $vendor->invoices->sum('doctotalamtr'),
                        'invoices' => [
                            'current' => $currentData->values()->all(),
                            'after' => $afterData->values()->all()
                        ],
                    ]
                ];
            });

            $sortedGroupedData = $groupedData->sortByDesc(function ($vendor) {
                return $vendor['vendor']['total'];
            })->values()->all();

            return [
                'vendors' => $sortedGroupedData,
                'current' => $currentTotal,
                'after' => $afterTotal,
                'total' => $totalData
            ];
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function getAgingDetailCustomer($cutOffDue, $code)
    {
        try {
            $vendor = customers::with(['invoices' => function ($query) {
                $query->select('custmrcode', 'docnum', 'docdate', 'docduedate', 'doctotalamtr')
                    ->where('swpaid', 0)
                    ->orderBy('docduedate', 'asc');
            }])->where('custmrcode', $code)->firstOrFail();

            $currentData = $vendor->invoices->filter(function ($invoice) use ($cutOffDue) {
                return Carbon::parse($invoice->docduedate)->lte($cutOffDue);
            });

            $afterData = $vendor->invoices->filter(function ($invoice) use ($cutOffDue) {
                return Carbon::parse($invoice->docduedate)->gt($cutOffDue);
            });

            return [
                'vendor' => [
                    'id' => $vendor->id,
                    'custmrcode' => $vendor->custmrcode,
                    'custmrname' => $vendor->custmrname,
                    'current' => $currentData->sum('doctotalamtr'),
                    'after' => $afterData->sum('doctotalamtr'),
                    'total' => $vendor->invoices->sum('doctotalamtr'),
                    'invoices' => [
                        'current' => $currentData->values()->all(),
                        'after' => $afterData->values()->all()
                    ],
                ]
            ];
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public function getData($type, $month = Null)
    {
        try {

            $queryBase = $this->connFourth->table($this->connFourth->raw('(
                SELECT invbl.custmrcode, cust.custmrname, invbl.docnum, invbl.docdate, invbl.docduedate, invbl.orderno, invbl.doctotalamt inv_amt, invbl.rcptotalamt rv_amt, invbl.doctotalamtr ar_amt FROM `ar_invobl` invbl
                INNER JOIN ar_customer cust  ON invbl.custmrcode=cust.custmrcode
                WHERE invbl.doctotalamtr != "0" AND invbl.orderno != "" AND invbl.yop = 2024) AS ag'))
                // ->select('ag.docdate', 'ag.custmrname', 'ag.docnum', 'ag.docdate', 'ag.docduedate', 'ag.orderno', 'ag.inv_amt', 'ag.rv_amt', 'ag.ar_amt')
                // ->orderBY('ag.custmrname', 'ASC')
                ->orderBY('ag.docdate', 'DESC');

            if ($type == 'thisMonth') {
                $queryBase = $queryBase->selectRaw('SUM(ag.ar_amt) AS ar_amt')->whereMonth('ag.docdate', $month)->whereYear('ag.docdate', Date::now()->year)->get();
                // $total = null;
                // foreach ($queryBase as $key) {
                //     $total += $key->ar_amt;
                // }
                // $queryBase = $total;
            } elseif ($type == 'total') {
                $queryBase = $queryBase->selectRaw('SUM(ag.ar_amt) AS ar_amt')->get();
                // $total = null;
                // foreach ($queryBase as $key) {
                //     $total += $key->ar_amt;
                // }
                // $queryBase = $total;
            } elseif ($type == 'overdue') {
            }


            return $queryBase;
        } catch (\Throwable $th) {
            return $th->getMessage();
            // return false;
        }
    }

    public function countData($month = null, $year = null)
    {
        try {
            if (!$month) {
                $month = date("n");
            }
            if (!$year) {
                $year = Date::now()->year;
            }
            // $month = Date::now();
            $agamttThisMonth = $this->getData('thisMonth', $month, $year);
            $totalAgamt = $this->getData('total', $month);
            $data = [
                'agamttThisMonth' => $agamttThisMonth,
                'totalAgamt' => $totalAgamt,
            ];
            return $data;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
