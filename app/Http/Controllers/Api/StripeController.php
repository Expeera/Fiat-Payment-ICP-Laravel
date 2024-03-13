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

            $stripe = new StripeService($request->get("secret_key"));
            $res = $stripe->createSession($request->toArray());

            if ($res['error'] ?? false) {
                return responseJson(false, $res['error']['message'], [], 422);
            }

            $invoiceNumber = $request->headers->get("invoice-number");
            Log::info($invoiceNumber);
            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, "");

//            $resCache = json_decode($invoiceNumberFromCache, true);
            if (empty($invoiceNumberFromCache)) {
                \Illuminate\Support\Facades\Cache::set($invoiceNumber, $res['id'] . ":-:" . $res["url"]);
            }

            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, "");
//            $resCache = json_decode($invoiceNumberFromCache , true);

            Log::info($invoiceNumber . " => return " . $invoiceNumberFromCache);
//$re = Random::generate(10);
//            Log::info("6- retuern success " . $re);

//        return responseJson(true, "Success", Random::generate(10), 200);

            $r = explode($invoiceNumber, ":-:");
            Log::info($r[0] . " => return " . $r[1]);

            return responseJson(true, "Success", json_encode([
                'id' => $r[0],
                'url' => $r[1],
            ]), 200);

        } catch (\Exception $e) {
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
