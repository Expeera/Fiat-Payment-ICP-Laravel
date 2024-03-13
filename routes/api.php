<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('stripe')->group(function () {
    Route::post('create-session', [\App\Http\Controllers\Api\StripeController::class, "sessions"]);
    Route::post('retrieve-session/{sessionId}', [\App\Http\Controllers\Api\StripeController::class, "retrieveSession"]);
});

Route::prefix('paypal')->group(function () {
    Route::post('create-order', [\App\Http\Controllers\Api\PaypalController::class, "createOrder"]);
    Route::post('retrieve-order', [\App\Http\Controllers\Api\PaypalController::class, "retrieveOrder"]);
});

Route::post('test', [\App\Http\Controllers\Api\StripeController::class, "test"]);
Route::get('cache', function (Request $request){
    $invoiceNumber = $request->get("invoice-number");
    echo("your invoice number: " . $invoiceNumber . "</br>");
    $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, json_encode([
        "id" => "",
        "url" => ""
    ]));
    $invoiceNumberFromCacheAfterDecode = json_decode($invoiceNumberFromCache , true);

//    echo($invoiceNumberFromCache. "</br>");
//    if($invoiceNumberFromCacheAfterDecode['is_call_api'] == false) {

        // excute stripe api

        $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, json_encode([
            "id" => "",
            "url" => ""
        ]));
        $resCache = json_decode($invoiceNumberFromCache , true);
        if(empty($resCache["id"]) ){
            \Illuminate\Support\Facades\Cache::set($invoiceNumber , [
                "is_call_api" => true,
                "id" => "",
                "url" => ""
            ]);
        }

    $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, json_encode([
        "id" => "",
        "url" => ""
    ]));
    $resCache = json_decode($invoiceNumberFromCache , true);



//        echo("Non from cache" . "</br>" );
//    }
});

