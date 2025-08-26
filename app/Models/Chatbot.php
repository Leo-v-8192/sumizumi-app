<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chatbot extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_name',
        'group_name',
        'api_key',
        'additional_prompt',
        'profile',
        'qa_example',
        'chatbot_color',
        'terms_url',
        'fixed_button_text', // <-- これを追加
        'fixed_button_url',  // <-- これを追加
    ];

    /**
     * Chatbotは自身のgroup_nameと同じgroup_nameを持つUserを全て取得する
     */
    public function users()
    {
        return $this->hasMany(User::class, 'group_name', 'group_name');
    }
}