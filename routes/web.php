<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->group(['prefix' => 'api'], function () use ($router) {

    //Database
    // $router->get('/distributor', 'ar\server1\scm_mb\OrderCustomerController@getData');
    $router->get('/order_customer', 'ar\server1\scm_mb\OrderCustomerController@getData');
    $router->get('/order_customer_detail', 'ar\server1\scm_mb\OrderCustomerDetailController@getData');
    $router->get('/release_order_customer', 'ar\server1\scm_mb\ReleaseOrderCustomerController@getData');
    $router->get('/order', 'ar\server1\tps\OrderController@getData');
    $router->get('/delivery', 'ar\server1\tps\DeliveryController@getData');
    $router->get('/wms_order', 'ar\server1\wms_tps\OrderController@getData');
    $router->get('/wms_delivery', 'ar\server1\wms_tps\DeliveryController@getData');
    $router->get('/arInvh', 'ar\server2\accapptps2023\ArInvhController@getData');
    $router->get('/rv', 'ar\server2\accapptps2023\CbBatchhController@getData');

    $router->get('/monitoring', 'ar\lokal\distributor\MonitoringController@getData');
    $router->get('/receipt', 'ar\lokal\distributor\MonitoringController@getReceipt');
    $router->get('/countdata', 'ar\lokal\distributor\MonitoringController@countData');
    $router->get('/searchdata', 'ar\lokal\distributor\MonitoringController@searchData');

    // $router->get('/arInvobl', 'ar\server2\accapptps2023\ArInvoblController@getData');
    $router->get('/getdataag', 'ar\server2\accapptps2023\ArInvoblController@getData');
    $router->get('/getdataag/{code}', 'ar\server2\accapptps2023\ArInvoblController@getInvoices');
    $router->get('/invoices', 'ar\server2\accapptps2023\ArInvoblController@getInvoices');
    $router->get('/countdata_ag', 'ar\server2\accapptps2023\ArInvoblController@countData');

    //router monitoring database pgsql
    $router->get('/user', 'MUserController@getData');

    //router user
    $router->get('/user/{id}', 'MUserController@show');
    $router->get('/api/users', 'MUserController@getUsers');
    $router->post('/user', 'MUserController@store');
    $router->delete('/user/{id}', 'MUserController@destroy');
    $router->put('/user/{id}', 'MUserController@update');
});
