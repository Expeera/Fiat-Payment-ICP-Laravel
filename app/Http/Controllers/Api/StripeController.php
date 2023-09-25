<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Stripe\CreateSessionRequest;
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
}
