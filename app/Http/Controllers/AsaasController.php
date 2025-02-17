<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AsaasController extends Controller
{
    public function index(Request $request){
        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', 'https://api.asaas.com/v3/pix/qrCodes/static', [
            'body' => '{"value":1}',
            'headers' => [
                'accept' => 'application/json',
                'access_token' => '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjU1YWFkZjcyLTNkNDQtNDAzOS04NzU2LTM2OWM3YjJmY2ZmMjo6JGFhY2hfYmUxYmZmY2MtNzYxYi00NTE5LTk1YzAtOTAwYTJjZTBiZTM1',
                'content-type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(),true);
        echo '<img src="data:image/jpeg;base64,'.($data['encodedImage']).'">';

        echo "<br>Payload: " . $data['payload'];
    }
}
