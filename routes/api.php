<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CyberBtcController;
use App\Http\Controllers\AdsManagerController;
use App\Http\Controllers\SanghaniBtcController;
use App\Http\Controllers\DhruvBtcController;
use App\Http\Controllers\DhruvUsdcController;
use App\Http\Controllers\NewUSDTControllers;
use App\Http\Controllers\WatchAndEarnSimulationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('/mail-to-sahil', [EmailController::class, 'sahilEnail']);
Route::post('/mail-to-rutvik', [EmailController::class, 'rutvikEnail']);

Route::post('/register', [UserController::class, 'registerUser']);
Route::post('/login', [UserController::class, 'loginUser']);
Route::post('/registerWithGoogle', [UserController::class, 'registerWithGoogle']);
Route::post('/verifyOtp', [UserController::class, 'verifyOtp']);
Route::post('/startMining', [UserController::class, 'startMining']);
Route::post('/getReferredUser', [UserController::class, 'getReferredUser']);
Route::delete('/deleteUser/{id}', [UserController::class, 'deleteUser']);
// Route::post('/getReferredUser' , function() {
//     return "hello";
// });

Route::post('/btc/register', [SanghaniBtcController::class, 'registerUser']);
Route::post('/btc/login', [SanghaniBtcController::class, 'loginUser']);
Route::post('/btc/registerWithGoogle', [SanghaniBtcController::class, 'registerWithGoogle']);
Route::post('/btc/verifyOtp', [SanghaniBtcController::class, 'verifyOtp']);
Route::post('/btc/startMining', [SanghaniBtcController::class, 'startMining']);
Route::post('/btc/getReferredUser', [SanghaniBtcController::class, 'getReferredUser']);
Route::delete('/btc/deleteUser/{id}', [SanghaniBtcController::class, 'deleteUser']);
Route::post('/btc/updateProfile' ,  [SanghaniBtcController::class, 'updateProfile']);
Route::post('/btc/updateMiningTime' ,  [SanghaniBtcController::class, 'updateminingTime']);
Route::post('/btc/deleteAccount' ,  [SanghaniBtcController::class, 'deleteAccount']);

// dhruv-sanghani
Route::post('/dhruv-btc/register', [DhruvBtcController::class, 'registerUser']);
Route::post('/dhruv-btc/login', [DhruvBtcController::class, 'loginUser']);
Route::post('/dhruv-btc/registerWithGoogle', [DhruvBtcController::class, 'registerWithGoogle']);
Route::post('/dhruv-btc/verifyOtp', [DhruvBtcController::class, 'verifyOtp']);
Route::post('/dhruv-btc/startMining', [DhruvBtcController::class, 'startMining']);
Route::post('/dhruv-btc/getReferredUser', [DhruvBtcController::class, 'getReferredUser']);
Route::delete('/dhruv-btc/deleteUser/{id}', [DhruvBtcController::class, 'deleteUser']);
Route::post('/dhruv-btc/updateProfile' ,  [DhruvBtcController::class, 'updateProfile']);
Route::post('/dhruv-btc/updateMiningTime' ,  [DhruvBtcController::class, 'updateminingTime']);
Route::post('/dhruv-btc/deleteAccount' ,  [DhruvBtcController::class, 'deleteAccount']);
Route::post('/dhruv-btc/uploadFeDoc', [DhruvBtcController::class, 'uploadFeDoc']);
Route::post('/dhruv-btc/uploadBeDoc', [DhruvBtcController::class, 'uploadBeDoc']);

// new-usdt
Route::post('/usdt/register', [NewUSDTControllers::class, 'registerUser']);
Route::post('/usdt/login', [NewUSDTControllers::class, 'loginUser']);
Route::post('/usdt/registerWithGoogle', [NewUSDTControllers::class, 'registerWithGoogle']);
Route::post('/usdt/verifyOtp', [NewUSDTControllers::class, 'verifyOtp']);
Route::post('/usdt/startMining', [NewUSDTControllers::class, 'startMining']);
Route::post('/usdt/getReferredUser', [NewUSDTControllers::class, 'getReferredUser']);
Route::delete('/usdt/deleteUser/{id}', [NewUSDTControllers::class, 'deleteUser']);
Route::post('/usdt/updateProfile' ,  [NewUSDTControllers::class, 'updateProfile']);
Route::post('/usdt/updateMiningTime' ,  [NewUSDTControllers::class, 'updateminingTime']);
Route::post('/usdt/updateSuperCoin' ,  [NewUSDTControllers::class, 'updateSuperCoin']);
Route::post('/usdt/deleteAccount' ,  [NewUSDTControllers::class, 'deleteAccount']);
Route::post('/usdt/sendTransfer' ,  [NewUSDTControllers::class, 'sendTransfer']);
Route::post('/usdt/reciveTransfer' ,  [NewUSDTControllers::class, 'reciveTransfer']);
Route::post('/usdt/collectTransfer' ,  [NewUSDTControllers::class, 'collectTransfer']);

Route::post('/cyber-btc/register', [CyberBtcController::class, 'registerUser']);
Route::post('/cyber-btc/login', [CyberBtcController::class, 'loginUser']);
Route::get('/cyber-btc/login/google',  [CyberBtcController::class, 'redirectToGoogle']);
Route::get('/cyber-btc/login/google/callback', [CyberBtcController::class, 'handleGoogleCallback']);
Route::post('/cyber-btc/forgotPassword', [CyberBtcController::class, 'forgotPassword']);
Route::post('/cyber-btc/changePassword', [CyberBtcController::class, 'changePassword']);

// Ads
Route::post("AdsManager" , [AdsManagerController::class, 'getAdsByPackgname']);
Route::post("DemoApi" , [AdsManagerController::class, 'demoApi']);

// Watch
Route::post('/watch/register', [WatchAndEarnSimulationController::class, 'registerUser']);
Route::post('/watch/login', [WatchAndEarnSimulationController::class, 'loginUser']);
Route::post('/watch/startActivate', [WatchAndEarnSimulationController::class, 'startActivate']);
Route::post('/watch/getReferredUser', [WatchAndEarnSimulationController::class, 'getReferredUser']);
Route::delete('/watch/deleteUser/{id}', [WatchAndEarnSimulationController::class, 'deleteUser']);
Route::post('/watch/updateProfile' ,  [WatchAndEarnSimulationController::class, 'updateProfile']);
Route::post('/watch/updateCoin' ,  [WatchAndEarnSimulationController::class, 'updateCoin']);
Route::post('/watch/deleteAccount' ,  [WatchAndEarnSimulationController::class, 'deleteAccount']);
