<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * ユーザー一覧を表示する
     */
    public function index()
    {
        $users = User::all();
        return view('admin.users.index', compact('users'));
    }

    /**
     * ユーザー作成フォームを表示する
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * 新しいユーザーをデータベースに保存する
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'group_name' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['boolean'],
        ]);

        // ▼▼▼【変更点】Hash::make()を削除。Userモデルの機能で自動的にハッシュ化されます ▼▼▼
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'group_name' => $request->group_name,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->route('admin.users.index')->with('status', 'ユーザーを新しく追加しました！');
    }

    /**
     * ユーザー編集フォームを表示する
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * ユーザー情報を更新する
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'group_name' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['boolean'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->group_name = $request->group_name;
        $user->is_admin = $request->boolean('is_admin');

        // ▼▼▼【変更点】Hash::make()を削除。Userモデルの機能で自動的にハッシュ化されます ▼▼▼
        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'ユーザー情報を更新しました！');
    }

    /**
     * ユーザーを削除する
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', '自分自身を削除することはできません。');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'ユーザーを削除しました。');
    }
}
