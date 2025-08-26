<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('管理者ページ - ChatBot管理') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- 成功メッセージ --}}
                    @if (session('status'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                            {{ session('status') }}
                        </div>
                    @endif
                    {{-- エラーメッセージ --}}
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.chatbots.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            新規ChatBot追加
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ChatBot名</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">グループ名</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">APIキー</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">操作</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($chatbots as $chatbot)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $chatbot->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $chatbot->chatbot_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $chatbot->group_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $chatbot->api_key }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex items-center justify-end">
                                            <a href="{{ route('admin.chatbots.edit', $chatbot) }}" class="text-indigo-600 hover:text-indigo-900">編集</a>
                                            
                                            {{-- ▼▼▼【変更点】「削除」をリンクからフォームに変更 ▼▼▼ --}}
                                            <form method="POST" action="{{ route('admin.chatbots.destroy', $chatbot) }}" class="ml-4" onsubmit="return confirm('本当にこのChatBotを削除しますか？');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">削除</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                            登録されているChatBotはありません。
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
