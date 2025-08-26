<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Chatbot;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. chatbotsテーブルにgroup_nameカラムを追加
        Schema::table('chatbots', function (Blueprint $table) {
            $table->string('group_name')->nullable()->after('chatbot_name');
        });

        // 2. 既存のデータを移行する
        // 全てのChatbotを取得し、user_idから持ち主のgroup_nameを見つけて設定する
        $chatbots = Chatbot::all();
        foreach ($chatbots as $chatbot) {
            if ($chatbot->user) { // ユーザーが存在する場合
                $chatbot->group_name = $chatbot->user->group_name;
                $chatbot->save();
            }
        }

        // 3. user_idカラムを削除
        Schema::table('chatbots', function (Blueprint $table) {
            // 外部キー制約を先に削除してからカラムを削除
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ロールバック（元に戻す）処理
        // 1. user_idカラムを戻す
        Schema::table('chatbots', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
        });

        // 2. 可能な範囲でデータを復元
        // group_nameから、そのグループに所属する最初のユーザーを探して紐づける
        $chatbots = Chatbot::all();
        foreach ($chatbots as $chatbot) {
            $user = User::where('group_name', $chatbot->group_name)->first();
            if ($user) {
                $chatbot->user_id = $user->id;
                $chatbot->save();
            }
        }

        // 3. group_nameカラムを削除
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn('group_name');
        });
    }
};
