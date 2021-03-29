<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'auth', 'namespace' => 'Api'], function() {
    Route::post('login', 'Auth\AuthController@login');
    Route::post('logout', 'Auth\AuthController@logout');
    Route::post('register', 'Auth\AuthController@register');
    Route::post('register_partner', 'Auth\AuthController@register_partner');
    Route::post('patner', 'Auth\AuthController@patner');
    Route::post('otp-confirmation', 'Auth\AuthController@otpConfirmation');
    Route::post('otp-patner-confirmation', 'Auth\AuthController@patnerOtpConfirmation');
});

Route::group(['middleware' => ['jwt.verify'], 'namespace' => 'Api'], function() {
    Route::get('profile', 'UserController@index');
    Route::post('edit_profile', 'UserController@edit_profile');
    Route::post('save_bookservice', 'OrderController@save_bookservice');
    Route::post('layanan_laundry', 'OrderController@layanan_laundry');
    Route::post('layanan_service', 'OrderController@layanan_service');
    Route::post('order_pending', 'OrderController@orders');    
	Route::post('order_reserved', 'OrderController@orders');	
	Route::post('order_going', 'OrderController@orders');	
	Route::post('order_completed', 'OrderController@orders');
    Route::post('order_detail', 'OrderController@order_detail');
	Route::post('syaratketentuan', 'UserController@syaratketentuan');
    Route::post('privasi', 'UserController@privasi');
    Route::post('aboutus', 'UserController@aboutus');
	Route::post('getbooking', 'UserController@getbooking');
    Route::get('patner-profile', 'UserController@patnerProfile');
    Route::post('orders_no_partner', 'OrderController@orders_no_partner');  
    Route::post('order_verifikasi', 'OrderController@order_verifikasi');  
    Route::post('history_orders', 'OrderController@HistoryOrder');  
    Route::post('update-order', 'OrderController@updateOrder');  
    Route::get('dataMembership', 'OrderController@dataMembership');  
    Route::post('paket_detail', 'OrderController@detailPaket');  
    Route::post('bayar', 'Payment\PaymentController@make_invoice');
    Route::get('data_pembayaran', 'Payment\PaymentController@data_invoice');
    
});


