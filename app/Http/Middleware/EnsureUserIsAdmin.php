<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ユーザーがログインしていて、かつ is_admin カラムが true (または 1) かどうかをチェック
        if (! $request->user() || ! $request->user()->is_admin) {
            // 条件に合わなければ、403 Forbidden（アクセス権がありません）エラーを返す
            abort(403, 'Unauthorized action.');
        }

        // 条件に合っていれば、リクエストを次の処理に進める
        return $next($request);
    }
}