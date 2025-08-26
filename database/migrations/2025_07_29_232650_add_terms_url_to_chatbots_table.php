<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            // chatbot_colorカラムの後に、terms_of_service_urlカラムを追加します。
            // URLは長くなる可能性があるので、TEXT型にしておくと安心です。
            $table->text('terms_url')->nullable()->after('chatbot_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chatbots', function (Blueprint $table) {
            $table->dropColumn('terms_url');
        });
    }
};
