<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatbot;
use Illuminate\Support\Str;

class ChatbotController extends Controller
{
    /**
     * Chatbot一覧を表示する
     */
    public function index()
    {
        $chatbots = Chatbot::all();
        return view('admin.chatbots.index', compact('chatbots'));
    }

    /**
     * Chatbot作成フォームを表示する
     */
    public function create()
    {
        return view('admin.chatbots.create');
    }

    /**
     * 新しいChatbotをデータベースに保存する
     */
    public function store(Request $request)
    {
        // ▼▼▼【変更点1】バリデーションにapi_keyを追加 ▼▼▼
        // chatbotsテーブル内でユニーク（重複不可）であることもチェック
        $request->validate([
            'chatbot_name' => ['required', 'string', 'max:255'],
            'group_name' => ['required', 'string', 'max:255'],
            'api_key' => ['required', 'string', 'max:255', 'unique:chatbots,api_key'],
        ]);

        // ▼▼▼【変更点2】リクエストから受け取ったapi_keyを保存するように変更 ▼▼▼
        Chatbot::create([
            'chatbot_name' => $request->chatbot_name,
            'group_name' => $request->group_name,
            'api_key' => $request->api_key, // 自動生成から手動入力に変更
        ]);

        return redirect()->route('admin.chatbots.index')->with('status', 'ChatBotを新しく追加しました！');
    }

    /**
     * Chatbot編集フォームを表示する
     */
    public function edit(Chatbot $chatbot)
    {
        return view('admin.chatbots.edit', compact('chatbot'));
    }

    /**
     * Chatbot情報を更新する
     */
    public function update(Request $request, Chatbot $chatbot)
    {
        $request->validate([
            'chatbot_name' => ['required', 'string', 'max:255'],
            'group_name' => ['required', 'string', 'max:255'],
            'api_key' => ['required', 'string', 'max:255'],
        ]);

        $chatbot->update($request->all());

        return redirect()->route('admin.chatbots.index')->with('status', 'ChatBot情報を更新しました！');
    }

    /**
     * Chatbotを削除する
     */
    public function destroy(Chatbot $chatbot)
    {
        $chatbot->delete();

        return redirect()->route('admin.chatbots.index')->with('status', 'ChatBotを削除しました。');
    }
}
