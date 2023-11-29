<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        
            $params = [
                'email' => $email,
                'password' => 'Unl1dWppbkAzMQ==',
                'deviceId' => '1234567890',
                'platformId' => $platformId,
            ];
        
            $url = 'https://servicebuss.transvision.co.id/tvs/login/external?' . http_build_query($params);
            $transService = Http::post($url);
            $transBody = $transService->json();
        
            if (!$transBody['access_token']) {
                throw new \Exception('error get session id');
            }
        
            $sessionId = $body['access_token'];
        
            return ['sessionId' => $sessionId, 'email' => $email];
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
        
            // Set the 'noTimestamp' option to true
            $options = [
                'noTimestamp' => true,
            ];
        
            // Sign the payload and get the token
            $token = JWTAuth::attempt($payload, $options);
        
            if (!$token) {
                throw new \Exception('Error signing the token');
            }
        
            // Split the token into its components
            list($encodedHeader, $encodedPayload, $encodedSignature) = explode('.', $token);
        
            return $encodedPayload;
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }

    public function getToken() {
        try {
            $login = $this->loginCubmu();
        
            if (empty($login['email']) || empty($login['sessionId'])) {
                throw new \Exception('Login failed');
            }
        
            $encodedToken = $this->encodeToken($login);
            $token = $encodedToken ?? '';
        
            return $token;
        } catch (\Exception $error) {
            throw new \Exception($error->getMessage());
        }
    }
}
