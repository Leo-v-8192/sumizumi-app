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
        // 既存のデータを移行します。
        // ユーザーごとに、group_nameが設定されている最初のchatbotを探し、
        // その値をユーザーのgroup_nameに設定します。
        $users = \App\Models\User::with('chatbots')->get();

        foreach ($users as $user) {
            $chatbotWithGroup = $user->chatbots()
                                    ->whereNotNull('group_name')
                                    ->where('group_name', '!=', '')
                                    ->first();

            if ($chatbotWithGroup) {
                $user->group_name = $chatbotWithGroup->group_name;
                $user->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        //
    }
};
