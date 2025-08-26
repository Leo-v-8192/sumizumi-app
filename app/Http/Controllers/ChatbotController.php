<?php

namespace App\Http\Controllers;

use App\Models\Chatbot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChatbotController extends Controller
{
    /**
     * 指定されたチャットボットを更新
     */
    public function update(Request $request, Chatbot $chatbot)
    {
        // ユーザーがこのChatbotを更新する権限があるかチェック
        $this->authorize('update', $chatbot);

        // ▼▼▼【変更点1】バリデーションルールに固定ボタンの項目を追加 ▼▼▼
        $request->validate([
            'additional_prompt' => ['nullable', 'string'],
            'profile' => ['nullable', 'string'],
            'questions.'.$chatbot->id => ['nullable', 'array'],
            'answers.'.$chatbot->id => ['nullable', 'array'],
            'chatbot_color' => ['nullable', 'string', 'regex:/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/'],
            'terms_url' => ['nullable', 'url'],
            'fixed_button_text' => ['nullable', 'string', 'max:255'], // ボタン名を追加
            'fixed_button_url' => ['nullable', 'url'],                 // ボタンURLを追加
        ]);

        // リクエストの中から、今更新しようとしているChatbotのIDに紐づくQ&Aデータだけを取り出す
        $questions = $request->input('questions.' . $chatbot->id, []);
        $answers = $request->input('answers.' . $chatbot->id, []);

        // Q&Aデータを指定のテキスト形式に整形する
        $qa_string = '';
        if (!empty($questions)) {
            $pair_count = 1;
            foreach ($questions as $index => $question) {
                if (!empty($question) && isset($answers[$index]) && !empty($answers[$index])) {
                    $qa_string .= "質問例{$pair_count}:" . $question . PHP_EOL;
                    $qa_string .= "回答例{$pair_count}:" . $answers[$index] . PHP_EOL;
                    $pair_count++;
                }
            }
        }

        // ▼▼▼【変更点2】更新データに固定ボタンの項目を追加 ▼▼▼
        $chatbot->update([
            'additional_prompt' => $request->additional_prompt,
            'profile' => $request->profile,
            'qa_example' => !empty($qa_string) ? rtrim($qa_string) : null,
            'chatbot_color' => $request->chatbot_color,
            'terms_url' => $request->terms_url,
            'fixed_button_text' => $request->fixed_button_text, // ボタン名を追加
            'fixed_button_url' => $request->fixed_button_url,   // ボタンURLを追加
        ]);

        return redirect()->route('dashboard')->with('status', 'Chatbotを更新しました！');
    }
}