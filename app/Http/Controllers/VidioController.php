<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

class VidioController extends Controller
{
    public function index()
    {
        try {
            $getToken = Http::acceptJson()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36'
            ])->post('https://www.vidio.com/live/733/tokens?type=dash');
            if ($getToken->failed()) {
                throw new \Exception("failed to get token with raw error " . $getToken->status());
            }

            $body = $getToken->json();
            $token = $body['token'];

            $getStream = Http::acceptJson()->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36'
            ])->get('https://etslive-2-vidio-com.secureswiftcontent.com/'. $token .'/vp9/733_stream.mpd');
            if ($getStream->failed()) {
                throw new \Exception("failed to get token with raw error " . $getStream->status());
            }

            $streamBody = $getStream->body();
        
            $response = Response::make($streamBody, 200);
            $response->headers->set("Content-type","application/dash+xml");

            return $response;
        } catch (\Throwable $th) {
            return response()->json([
                "error" => $th->getMessage(),
            ], 400);
        }
    }
}
