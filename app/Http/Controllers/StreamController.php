<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
                throw new \Exception("failed to get token with raw error" . $reqToken);
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
