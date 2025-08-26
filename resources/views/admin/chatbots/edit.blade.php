<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('管理者ページ - ChatBot編集') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- バリデーションエラーがある場合に表示 --}}
                    @if ($errors->any())
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- 更新用のフォーム。メソッドはPATCH、アクションのURLには編集対象のChatbot IDを含める --}}
                    <form method="POST" action="{{ route('admin.chatbots.update', $chatbot) }}">
                        @csrf
                        @method('PATCH') {{-- 更新リクエストであることを示す --}}

                        <!-- ChatBot名 -->
                        <div>
                            <x-input-label for="chatbot_name" :value="__('ChatBot Name')" />
                            {{-- old()の第2引数に$chatbotの情報を渡して、既存の値を表示 --}}
                            <x-text-input id="chatbot_name" class="block mt-1 w-full" type="text" name="chatbot_name" :value="old('chatbot_name', $chatbot->chatbot_name)" required autofocus />
                            <x-input-error :messages="$errors->get('chatbot_name')" class="mt-2" />
                        </div>

                        <!-- グループ名 -->
                        <div class="mt-4">
                            <x-input-label for="group_name" :value="__('Group Name')" />
                            <x-text-input id="group_name" class="block mt-1 w-full" type="text" name="group_name" :value="old('group_name', $chatbot->group_name)" required />
                            <x-input-error :messages="$errors->get('group_name')" class="mt-2" />
                        </div>

                        <!-- APIキー -->
                        <div class="mt-4">
                            <x-input-label for="api_key" :value="__('API Key')" />
                            <x-text-input id="api_key" class="block mt-1 w-full font-mono" type="text" name="api_key" :value="old('api_key', $chatbot->api_key)" required />
                            <x-input-error :messages="$errors->get('api_key')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('admin.chatbots.index') }}">
                                {{ __('一覧に戻る') }}
                            </a>

                            <x-primary-button class="ml-4">
                                {{ __('更新する') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
