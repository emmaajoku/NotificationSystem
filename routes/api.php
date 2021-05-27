<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\{AuthController, HookController, NotificationsController};

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });

    // register as a user to obtain an access token
    Route::post('register', [AuthController::class, 'register']);

    //login as user to obtain an access token
    Route::post('login', [AuthController::class, 'login']);

    // create a hook kind of message for the subscriber
    Route::post('create-message', [HookController::class, 'store'])->middleware('auth:api');

    // subscribe to a notification
    Route::post('/subscribe/{webhook}', [NotificationsController::class, 'subscribe'])->middleware('auth:api');

    // subscribe to a notification to a notification
    Route::post('/publish/{webhook}', [HookController::class, 'dispatchNotification'])->middleware('auth:api');

