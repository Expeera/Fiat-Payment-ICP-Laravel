<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\CreateSessionRequest;
use App\Http\Requests\Stripe\RetrieveSessionRequest;
use App\Services\StripeService;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function sessions(CreateSessionRequest $request)
    {
        try {

            $stripe = new StripeService($request->get("secret_key"));
            $res = $stripe->createSession($request->toArray());

            if ($res['error'] ?? false) {
                return responseJson(false, $res['error']['message'], [], 422);
            }

            return responseJson(true, "Success", [
                'id' => $res['id'],
                'url' => $res['url'],
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
