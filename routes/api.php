<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Chatbot;

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

// ▼▼▼【変更点】返すJSONの項目に固定ボタンの情報を追加 ▼▼▼
Route::get('/chatbots/{chatbot:api_key}', function (Chatbot $chatbot) {
    return response()->json(
        [
            'additional_prompt' => $chatbot->additional_prompt,
            'profile' => $chatbot->profile,
            'qa_example' => $chatbot->qa_example,
            'chatbot_color' => $chatbot->chatbot_color,
            'terms_url' => $chatbot->terms_url,
            'fixed_button_text' => $chatbot->fixed_button_text, // fixed_button_textを追加
            'fixed_button_url' => $chatbot->fixed_button_url,   // fixed_button_urlを追加
        ],
        200,
        [],
        JSON_UNESCAPED_UNICODE
    );
});
