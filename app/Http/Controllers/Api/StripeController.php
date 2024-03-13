<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\CreateSessionRequest;
use App\Http\Requests\Stripe\RetrieveSessionRequest;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nette\Utils\Random;

class StripeController extends Controller
{

    public function test(Request $request)
    {
        try {

            $re = Random::generate(10);

            return responseJson(true, "Success", $re, 200);

        } catch (\Exception $e) {
            return responseJson(false, $e->getMessage(), [], 500);
        }
    }

    public function sessions(CreateSessionRequest $request)
    {
        try {

            Log::info("1- Get Secret key");
            $stripe = new StripeService($request->get("secret_key"));
            Log::info("2- Start to create session");

            $res = $stripe->createSession($request->toArray());
            Log::info("3- End to create session");

            Log::info("4- Check if there an error");

            if ($res['error'] ?? false) {
                Log::info("5- retuern error ");

                return responseJson(false, $res['error']['message'], [], 422);
            }

            $invoiceNumber = $request->headers->get("invoice-number");

            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, json_encode([
                "id" => "",
                "url" => ""
            ]));
//
            $resCache = json_decode($invoiceNumberFromCache , true);
            if(empty($resCache["id"]) ){
                \Illuminate\Support\Facades\Cache::set($invoiceNumber , [
                    "id" => $res['id'],
                    "url" => $res['url'],
                ]);
            }

            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, json_encode([
                "id" => "",
                "url" => ""
            ]));
            $resCache = json_decode($invoiceNumberFromCache , true);

//            Log::info("6- retuern success " . $res['id']);
//$re = Random::generate(10);
//            Log::info("6- retuern success " . $re);

//        return responseJson(true, "Success", Random::generate(10), 200);

            return responseJson(true, "Success", json_encode([
                'id' => $resCache['id'],
                'url' => $resCache['url'],
            ]), 200);

        } catch (\Exception $e) {
            Log::info("retuern execption ");

            return responseJson(false, $e->getMessage(), [], 500);
        }
    }

    public function retrieveSession($sessionId, RetrieveSessionRequest $request)
    {
        try {

            $stripe = new StripeService($request->get("secret_key"));
            $res = $stripe->retrieveSession($sessionId);

            if ($res['error'] ?? false) {
                return responseJson(false, $res['error']['message'], [], 422);
            }

            return responseJson(true, "Success", [
                'payment_status' => $res['payment_status'],
            ], 200);

        } catch (\Exception $e) {
            return responseJson(false, $e->getMessage(), [], 500);
        }
    }
}
