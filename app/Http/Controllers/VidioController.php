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
                'authority' => 'www.vidio.com',
                'Accept' => '*/*',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Cookie' => 'ahoy_visitor=8359ca0c-cf31-45cd-a5e2-8acc0cc26d9e; _gcl_au=1.1.1920583229.1700139008; afUserId=f9058094-68ee-4975-893a-435a3b6fdbce-p; AF_SYNC=1700840253148; _vidio=true; plenty_id=147196597; _gid=GA1.2.1079262363.1701437618; _ce.irv=returning; cebs=1; _ce.clock_event=1; _ce.clock_data=-6733%2C103.135.226.21%2C1%2Cf529a32073a22388a8370c39e9b93c86; moe_uuid=5f68e402-5528-44c8-9586-c2955ae02d57; g_state={"i_p":1702042441460,"i_l":3}; shva=1; access_token=eyJhbGciOiJIUzI1NiJ9.eyJkYXRhIjp7InR5cGUiOiJhY2Nlc3NfdG9rZW4iLCJ1aWQiOjE0NzE5NjU5N30sImV4cCI6MTcwMTUyNDA2MX0.tduPS3JyHvs98hHnswA9dgTyFNlJQOsxDU4u6GOTaBo; user_segments=meiro_movie_series_quality_watcher%2Cmeiro_svod_users%2Cmeiro_kids_content_rating%2Cmeiro_non_tv_quality_watcher_male%2Cmeiro_active_subs_plat_dmd%2Cmeiro_sports_last_ten_play%2Cmeiro_svod_homepage_last7days%2Cmeiro_svod_kids_content_rating; luws=f7369318661b0c3a97a5f22f_147196597; cebsp_=17; _ga_JBTBSESXVN=GS1.1.1701440650.8.0.1701440650.60.0.0; _ce.s=v~79235fc13aece1e42a9f3e0e291f7b99bb3e8f75~lcw~1701440650809~lva~1701437618443~vpv~5~as~false~v11.fhb~1701437618708~v11.lhb~1701440642363~v11.cs~265059~v11.s~3c366270-904e-11ee-b47c-8b04e4cb64d6~v11.sla~1701440640626~gtrk.la~lpmpu0ch~gtrk.cnv~7cd~v11.send~1701440650809~lcw~1701440650817; ahoy_visit=a3170178-9dfd-4791-92a0-f331a656d1c3; _ga=GA1.2.710650378.1700139008; _vidio_session=cDVnQWZlKzkxUUF3TjJBMGNQVDMyQ0t3YlJQNklIend4cVh5Zll2b1pqM1c4Q0dpZHNkb285SHdHZnVuV0UvZGVYZmJmbjRHVWFINmJuNDRrWkpCRHdMT3RPTjRDUHUvdUJMbE5sNjJrMWdUM2l0MEVEUnowMjZjUkFxQm10NThKY2FQaDFWSE9hRkgva1kwTW5KcWNMK3VKTWFvN0phK2srR1QvNXBLc2VaMUdNY2JoZWl5THIxYjI4VktJMVhBMU5MSUtOOFNQZGlGdS9FQ3BGYldGZ1JtMFI4QUFKQ0FzWU9DYXJ4RzJLV2dpcEVqT3FqK3JsaVc4cVdWT2RET3dBNEY1ckNNa0VwSnNSaWx0dEcycldyN1oxRGIza1V6a1NueTFmcU96L2xnelVJK2pNRGtzOTNhT01hYUhYRzh1S2pxUzdlbGRxOVBkTGtyNi9zUlBZR0NnQ2NiR2psQkVFNVZyUjQyeTd0eDVGTzZjd0pxeEF3Ykx5d0lpdlZNYkpHUS9PZUVGNnJCdnVUbVVZQkMwaFR5Mk1kVFBOOHd6Yi82RTRaTHJYZjhsWjNDUWUxODhkV25XcHhKM1M1RGovcUJ0UFIrUDBNWWwvUmxsdkdYbHA5Y0Y3MXI3eGUwekMvdHFuak9OSkpCT2hsQ2RabmhXZDlnc20yUEVTcXdSZEtsdG9HMDRtVjJBQU0wam94cG1rUFVieXBTRVZhdEJieUxJaFpsTU5CWWJvTlpOcUxIRG9TSzNUc2FIeVZQR0t0dlNFVGNnR2wrVnZZTDVwbEVrWVQxSDAzMzI3WDY1VUtYRldlQ3RlMWFtb2Y2UWYvRmxLd3EwNmpCd3cvdjh2bEJSWkMzbnVteDRkODB2ZU1IYW14Y001dzlmZ1RyUnFNWWRvMHBmcGc9LS0vZjNiWG5TajZvYWUyaE9EQStDeVBnPT0%3D--49d6b9bc6dec1f8dab3720af9e358f640fe3a09e',
                'Origin' => 'https://www.vidio.com',
                'Referer' => 'https://www.vidio.com/live/733-trans-tv',
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
                throw new \Exception("failed to get stream with raw error " . $getStream->status());
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
