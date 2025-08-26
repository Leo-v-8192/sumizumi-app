<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            // qa_exampleカラムの後に、chatbot_colorカラムを追加します。
            // 7文字の文字列（例: #aabbcc）を保存できれば良いので、string型で文字数を指定します。
            // 色が設定されていない場合も考慮し、nullable()を付けます。
            $table->string('chatbot_color', 7)->nullable()->after('qa_example');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn('chatbot_color');
        });
    }
};