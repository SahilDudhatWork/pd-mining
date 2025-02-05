<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AdsManagerModel;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class AdsManagerController extends Controller
{

    function getAdsByPackgname(Request $request) {

        try {
            $request->validate([
                'packgname' => 'required|string'
            ]);

            $package = AdsManagerModel::where('packgname',$request->packgname)->first();

            if ($package) {
            
                $adsData = $package->ads;
                $array = json_decode($adsData, true);
                
                // $ip = $request->ip();
                // $response = Http::get("http://ip-api.com/json/$ip");
                // $data = $response->json();
                // $countryCode = $data['countryCode'];
                // if($countryCode == 'US' || $countryCode == 'CA') {
                //     $array['ADS_TYPE'] = 6;
                //     $array['ADS_CHANGE_COUNTER'] = 10;
                //     return response()->json(['status' => 'success', 'ads' => $array]);
                // }
                
                return response()->json(['status' => 'success', 'ads' => $array]);
            } else {
                // Record not found
                return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'packgname is reqired'], 404);
        }
    }
    
    
    function demoApi(Request $request) {
             if ($request->packgname == "demo.api") {
                           $data = [
            "SHOW_GAME" => true,
            "GAME_LINK" => "https://gamezop.com/",
            "GAME_IMAGES" => [
                "https://unityitsolution.com/storage/GameZop/game_banner1.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner2.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner3.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner4.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner1.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner2.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner3.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner4.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner1.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner2.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner3.jpg",
                "https://unityitsolution.com/storage/GameZop/game_banner4.jpg"
            ]
        ];
                return response()->json(['status' => 'success', 'data' => $data]);
            } else {
                // Record not found
                return response()->json(['status' => 'error', 'message' => 'Record not found'], 404);
            }
       
    }
}
