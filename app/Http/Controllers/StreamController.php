<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;

class StreamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indosiar()
    {
        try {
            // Get Token
            $reqToken = Http::acceptJson()->withHeaders([
                'Origin' => 'https://www.vidio.com',
                'Referer' => 'https://www.vidio.com/live/205-indosiar',
            ])->post("https://www.vidio.com/live/205/tokens");
            if ($reqToken->failed()) {
                throw new \Exception("failed to get token with raw error " . $reqToken);
            }

            $tokenJson = $reqToken->json();
            $token = $tokenJson["token"];

            $reqMaster = Http::acceptJson()->withHeaders([
                'Origin' => 'https://www.vidio.com',
                'Referer' => 'https://www.vidio.com/',
            ])->get("https://etslive-app.vidio.com/live/205/master.m3u8?".$token);

            if ($reqMaster->failed()) {
                throw new \Exception("failed to get master");
            }

            $masterBody = $reqMaster->body();

            $pattern = "/https:\/\/etslive-2-vidio-com\.akamaized\.net\/[^ \n]+/";
            $match = preg_match($pattern, $masterBody, $matches);
            if ($match) {
                $url = $matches[0];
            } else {
                $url = "";
            }

            return $url;
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
            ], 400);
        }
    }

    public function loginCubmu() {
        try {
            $loginPayload = [
                [
                    'url' => '/v2/auth/login',
                ],
                [
                    'app_id' => 'cubmu',
                    'tvs_platform_id' => 'standalone',
                    'email' => 'andre.ndr31@gmail.com',
                    'password' => 'xxeHhVbmwxZFdwcGJrQXpNWHRUVUV4SlZGUkZVbjB4TnpBd05EZzJOVEF5',
                ]
            ];
        
            $loginResponse = Http::post('https://www.cubmu.com/api/hmac', $loginPayload);
            $body = $loginResponse->json();
        
            if ($body['statusCode'] !== '200') {
                throw new \Exception('error get access token');
            }

            $platformId = $body['result']['platform_id'];
            $email = $body['result']['email'];
            $userEmailPlatform = [
                'platformId' => $platformId,
                'email' => $email
            ];

            return $userEmailPlatform;
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }

    public function getAccessToken() {
        try {
            $params = [
                'email' => 'andre.ndr31_std@gmail.com',
                'password' => 'Unl1dWppbkAzMQ==',
                'deviceId' => '1234567890',
                'platformId' => '4028c68574537fcd0174af6756a94288',
            ];
        
            $url = 'https://servicebuss.transvision.co.id/tvs/login/external?' . http_build_query($params);
            $transService = Http::post($url);
            $transBody = $transService->json();
        
            if (!$transBody['access_token']) {
                throw new \Exception('error get session id');
            }

            $sessionId = $transBody['access_token'];
            $userData = ['sessionId' => $sessionId, 'email' => $params['email']];

            return $userData;
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }

    public function encodeToken($req) {
        try {
            $payload = [
                'userId' => $req['email'],
                'sessionId' => $req['sessionId'],
                'merchant' => 'giitd_transvision',
            ];
        
            // Sign the payload and get the token
            $factory = JWTFactory::customClaims($payload);
            $maker = $factory->make(true);
            $token = JWTAuth::encode($maker);

            if (!$token) {
                throw new \Exception('Error signing the token');
            }
        
            // Split the token into its components
            list($encodedHeader, $encodedPayload, $encodedSignature) = explode('.', $token);
        
            $redisBody = json_encode($encodedPayload);
            $expire = 86400; // 1 Day
            // Redis::set('cubmu', $redisBody, 'EX', $expire);

            return $encodedPayload;
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }

    public function getToken() {
        try {
            // $cubmu = Redis::get('cubmu');
            // if (!empty($cubmu)) {
            //     $cubmuToken = json_decode($cubmu, true);
            // } else {
                $tokenPayload = $this->getAccessToken();
            
                if (empty($tokenPayload['email']) || empty($tokenPayload['sessionId'])) {
                    throw new \Exception('Login failed');
                }

                $cubmuToken = $this->encodeToken($tokenPayload);
            // }
        
            $token = $cubmuToken ?? '';
        
            return response($token)->header('Content-Type', 'application/json');
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }

    public function getLicenseDRMToday(Request $request) {
        try {
            $payload = $request->getContent();
            $getToken = $this->getToken();
            $token = $getToken->getContent();

            $params = [
                'url' => 'https://lic.drmtoday.com/license-proxy-widevine/cenc/'
            ];
            $http = Http::withHeaders([
                'dt-custom-data' => $token,
            ])->withQueryParameters([
                'specConform' => 'true',
            ])->withBody($payload)->post($params['url']);

            $responseBody = $http->getBody();

            return response($responseBody, 200)->header('Content-Type', 'text/plain');
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }
}
