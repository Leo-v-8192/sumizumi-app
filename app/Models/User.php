<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
// ▼▼▼【変更点1】Hashをインポート ▼▼▼
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'is_admin',
        'password',
        'group_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        // ▼▼▼【変更点2】'password' => 'hashed' の行を削除 ▼▼▼
        'is_admin' => 'boolean',
    ];

    /**
     * ▼▼▼【変更点3】パスワードを自動的にハッシュ化するメソッドを追加 ▼▼▼
     *
     * @param  string  $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        // パスワードが設定される際に、自動でHash::make()を実行する
        $this->attributes['password'] = Hash::make($value);
    }

    /**
     * ユーザーとChatbotの関係性を再定義
     */
    public function chatbots()
    {
        return $this->hasMany(Chatbot::class, 'group_name', 'group_name');
    }
}