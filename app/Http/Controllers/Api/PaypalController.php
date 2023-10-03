<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Paypal\CreateOrderRequest;
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

            $link = collect($res['links'])->where('rel' , 'approve')->first()['href'] ?? '';

            return responseJson(true, "Success", [
                'id' => $res['id'],
                'url' => $link,
            ], 200);

        } catch (\Exception $e) {
            return responseJson(false, $e->getMessage(), [], 500);
        }
    }

    public function retrieveOrder(Request $request)
    {
        try {


        } catch (\Exception $e) {
            return responseJson(false, $e->getMessage(), [], 500);
        }
    }
}
