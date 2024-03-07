<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\CreateSessionRequest;
use App\Http\Requests\Stripe\RetrieveSessionRequest;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeController extends Controller
{

    public function test(Request $request)
    {
        try {

            return responseJson(true, "Success", ["test" => "testing"], 200);

        } catch (\Exception $e) {
            return responseJson(false, $e->getMessage(), [], 500);
        }
    }

    public function sessions(CreateSessionRequest $request)
    {
        try {



            Log::info("Get Secret key");
            $stripe = new StripeService($request->get("secret_key"));
            Log::info("Start to create session");


$data = $request->toArray();
//            $res = $stripe->createSession($request->toArray());

            $formData = [
                'payment_method_types[0]' => 'card',
                'line_items[0][price_data][currency]' => $data['currency'],
                'line_items[0][price_data][unit_amount]' => $data['unit_amount'],
                'line_items[0][quantity]' => $data['quantity'],
                'line_items[0][price_data][product_data][name]' => 'Token',
                'mode' => 'payment',
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url']
            ];

            $postData = http_build_query($formData);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.stripe.com/v1/checkout/sessions',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $this->secretKey,
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $res = curl_exec($curl);
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            Log::info("End to create session");

            Log::info("Check if there an error");

            if ($res['error'] ?? false) {
                Log::info("retuern error ");

                return responseJson(false, $res['error']['message'], [], 422);
            }

            Log::info("retuern success " . $res['id']);


            return responseJson(true, "Success", [
                'id' => $res['id'],
                'url' => $res['url'],
            ], 200);

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
