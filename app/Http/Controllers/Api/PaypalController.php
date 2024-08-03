<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paypal\CreateOrderRequest;
use App\Http\Requests\Paypal\RetrieveOrderRequest;
use App\Services\PaypalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaypalController extends Controller
{
    public function createOrder(CreateOrderRequest $request)
    {
        try {
            $paypalService = new PaypalService($request->get("client_id"), $request->get("client_secret"));
            $res = $paypalService->createOrder($request->toArray());

            if ($res['message'] ?? false) {
                return responseJson(false, $res['message'], [], 422);
            }

            $link = collect($res['links'])->where('rel', 'approve')->first()['href'] ?? '';

            $invoiceNumber = $request->headers->get("invoice-number");
            $invoiceNumberFromCache = \Illuminate\Support\Facades\Cache::get($invoiceNumber, "");
            if (empty($invoiceNumberFromCache)) {
                \Illuminate\Support\Facades\Cache::set($invoiceNumber, $res['id'] . ":-:" . $link, 300); // 300 seconds = 5 minutes
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

    public function retrieveOrder(RetrieveOrderRequest $request)
    {
        try {

            $paypalService = new PaypalService($request->get("client_id"), $request->get("client_secret"));
            $res = $paypalService->retrieveOrder($request->get("order_id"));

            if ($res['message'] ?? false) {
                return responseJson(false, $res['message'], [], 422);
            }
            Log::info("1-" . $res['status']);

            if ($res['status'] == "APPROVED") {
                $res = $paypalService->captureOrder($request->get("order_id"));
                if ($res['message'] ?? false) {
                    return responseJson(false, $res['message'], [], 422);
                }
            }

            Log::info("2-" . $res['status']);

            if($res['status'] == "COMPLETED"){

                Log::info("result-" . $res['status']);

                return responseJson(true, "Success", [
                    'status' => $res['status'],
                ], 200);
            }else {
                return responseJson(false, "The order is not complete", [], 500);
            }


        } catch (\Exception $e) {
            return responseJson(false, $e->getMessage(), [], 500);
        }
    }
}
