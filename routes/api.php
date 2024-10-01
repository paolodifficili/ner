<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MuxController;
use App\Http\Controllers\QueueController;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get($uri, $callback);
Route::post($uri, $callback);
Route::put($uri, $callback);
Route::patch($uri, $callback);
Route::delete($uri, $callback);
Route::options($uri, $callback);

*/

Route::get('/coda/show', [QueueController::class, 'showCoda']);

Route::get('/upload/list', [QueueController::class, 'showUploadFileList']);
Route::get('/config/list', [QueueController::class, 'showConfig']);
Route::get('/action/list', [QueueController::class, 'showBatchAction']);

Route::get('/batch/{id?}', [QueueController::class, 'showBatch']);

Route::put('/batch/{id}', [QueueController::class, 'changeBatch']);

Route::post('/batch', [QueueController::class, 'storeBatch']);

// action manager batch
Route::post('/qmgr', [QueueController::class, 'mgrBatch']);

// MUX Routes for upload 

Route::get('/mux', function() {
    Log::channel('stack')->info('mux get:', []);
    header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: *");
	header("Access-Control-Allow-Headers: *");
	http_response_code(200);
});

Route::options('/mux', function() {
    Log::channel('stack')->info('mux options:', []);
    header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: *");
	header("Access-Control-Allow-Headers: *");
	http_response_code(200);
});

Route::options('/head', function() {
    Log::channel('stack')->info('mux head:', []);
    header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: *");
	header("Access-Control-Allow-Headers: *");
	http_response_code(200);
});

Route::put('/mux', [MuxController::class, 'update']);

/*
Route::put('/mux', function() {
    Log::channel('stack')->info('mux put:', []);



    header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: *");
	header("Access-Control-Allow-Headers: *");
	http_response_code(200);
});
*/



