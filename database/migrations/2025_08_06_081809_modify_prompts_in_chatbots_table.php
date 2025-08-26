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
        // ▼▼▼【変更点】処理を2つのステップに分割 ▼▼▼

        // ステップ1: system_prompt カラムを additional_prompt にリネーム
        Schema::table('chatbots', function (Blueprint $table) {
            $table->renameColumn('system_prompt', 'additional_prompt');
        });

        // ステップ2: 新しく profile カラムを追加
        Schema::table('chatbots', function (Blueprint $table) {
            $table->text('profile')->nullable()->after('additional_prompt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 元に戻す処理も安全のために分割
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn('profile');
        });
        Schema::table('chatbots', function (Blueprint $table) {
            $table->renameColumn('additional_prompt', 'system_prompt');
        });
    }
};