<?php

namespace App\Services;


use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;

class StripeService
{

    private $secretKey;
    private $baseUrl = "https://api.stripe.com/v1";


    public function __construct($secretKey)
    {
        $this->secretKey = $secretKey;
    }


    public function createSession($data)
    {

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
            CURLOPT_URL => $this->baseUrl . '/checkout/sessions',
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

        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return json_decode($response, true);
    }

    public function retrieveSession($sessionId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->baseUrl . '/checkout/sessions/' . $sessionId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->secretKey,
            ),
        ));

        $response = curl_exec($curl);
        $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return json_decode($response, true);
    }


}
