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
            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, "");
            if (empty($invoiceNumberFromCache)) {
                \Illuminate\Support\Facades\Cache::set($invoiceNumber, $res['id'] . ":-:" . $res["url"], 60); // 60 seconds = 1 minutes
            }
            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, "");
            $r = explode(":-:", $invoiceNumberFromCache);

            return responseJson(true, "Success", [
                'id' => $r[0],
                'url' => $r[1],
            ], 200);

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
