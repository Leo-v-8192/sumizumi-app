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
            // terms_urlカラムの後に、新しいカラムを2つ追加します。
            $table->string('fixed_button_text')->nullable()->after('terms_url');
            $table->text('fixed_button_url')->nullable()->after('fixed_button_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            // カラムを削除する処理
            $table->dropColumn(['fixed_button_text', 'fixed_button_url']);
        });
    }
};