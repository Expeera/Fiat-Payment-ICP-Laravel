<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paypal\CreateOrderRequest;
use App\Http\Requests\Paypal\RetrieveOrderRequest;
use App\Services\PaypalService;
use Illuminate\Http\Request;

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

            return responseJson(true, "Success", [
                'id' => $res['id'],
                'url' => $link,
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
            if ($res['status'] == "APPROVED") {
                $res = $paypalService->captureOrder($request->get("order_id"));
                if ($res['message'] ?? false) {
                    return responseJson(false, $res['message'], [], 422);
                }
            }

            if($res['status'] == "COMPLETED"){
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
