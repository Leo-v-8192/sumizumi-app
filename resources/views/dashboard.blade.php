<x-app-layout>
    {{-- ページ全体で「設定」と「ログ」を切り替えるためのAlpine.jsコンポーネントを定義 --}}
    <div x-data="{ mainTab: 'settings' }">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        {{-- ユーザー名と、新しくユーザーに紐付いたグループ名を表示 --}}
                        <h1 class="text-xl font-bold">こんにちは、{{ Auth::user()->name }}さん！</h1>
                        <p class="text-sm text-gray-600 mt-1">あなたのグループ名: <span class="font-semibold">{{ Auth::user()->group_name ?? '未設定' }}</span></p>

                        {{-- ページ全体のタブナビゲーションを新設 --}}
                        <div class="mt-6 border-b border-gray-200">
                            <nav class="-mb-px flex space-x-6" aria-label="Tabs">
                                <button @click="mainTab = 'settings'"
                                        :class="{ 'border-indigo-500 text-indigo-600': mainTab === 'settings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': mainTab !== 'settings' }"
                                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-base">
                                    ChatBot設定
                                </button>
                                <button @click="mainTab = 'logs'"
                                        :class="{ 'border-indigo-500 text-indigo-600': mainTab === 'logs', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': mainTab !== 'logs' }"
                                        class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-base">
                                    ログ管理
                                </button>
                            </nav>
                        </div>
                    </div>

                    {{-- Alpine.jsで選択されたChatbotのIDを管理 --}}
                    <div x-show="mainTab === 'settings'" x-data="{ selectedChatbotId: {{ $chatbots->first()->id ?? 'null' }} }">
                        <div class="p-6 text-gray-900">
                            {{-- 成功メッセージを2秒で自動的に閉じるように設定 --}}
                            @if (session('status'))
                                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 2000)"
                                     class="mb-4 p-4 bg-green-100 text-green-700 rounded transition-opacity duration-500"
                                     x-transition:leave="opacity-0">
                                    {{ session('status') }}
                                </div>
                            @endif
                            @if (session('error') && session('from') === 'chatbot_update')
                                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if($chatbots->isNotEmpty())
                                {{-- Chatbot選択用のプルダウンを設置 --}}
                                <div class="mb-6 max-w-xs">
                                    <label for="chatbot_selector" class="block text-sm font-medium text-gray-700">編集するChatBotを選択</label>
                                    <select id="chatbot_selector" x-model="selectedChatbotId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        @foreach($chatbots as $chatbot)
                                            <option value="{{ $chatbot->id }}">{{ $chatbot->chatbot_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-6">
                                    @foreach($chatbots as $chatbot)
                                        {{-- 選択されたChatbotの設定のみ表示 --}}
                                        <div x-show="selectedChatbotId == {{ $chatbot->id }}" x-cloak>
                                            <div class="p-4 bg-gray-100 rounded-lg shadow chatbot-form" 
                                                 x-data='{ 
                                                     "tab": "prompt",
                                                     "pc_position": "{{ old('pc_position', $chatbot->pc_position ?? 'left') }}",
                                                     "sp_position": "{{ old('sp_position', $chatbot->sp_position ?? 'left') }}",
                                                     "breakpoint": {{ old('breakpoint', $chatbot->breakpoint ?? 768) }},
                                                     "copied": false,
                                                     generateEmbedCode() {
                                                         const botName = "{{ addslashes($chatbot->chatbot_name) }}";
                                                         return `<script src="https://aic-dr.com/ai-sumizumi/_common/chatbot.js" data-bot-id="${botName}" data-pc-position="${this.pc_position}" data-sp-position="${this.sp_position}" data-breakpoint="${this.breakpoint}"><\/script>`;
                                                     },
                                                     copyToClipboard() {
                                                         const codeToCopy = this.generateEmbedCode();
                                                         const textarea = document.createElement("textarea");
                                                         textarea.value = codeToCopy;
                                                         document.body.appendChild(textarea);
                                                         textarea.select();
                                                         document.execCommand("copy");
                                                         document.body.removeChild(textarea);
                                                         
                                                         this.copied = true;
                                                         setTimeout(() => { this.copied = false }, 2000);
                                                     }
                                                 }' 
                                                 data-chatbot-id="{{ $chatbot->id }}">
                                                
                                                <h4 class="font-semibold">{{ $chatbot->chatbot_name }}</h4>
                                                
                                                <div class="mt-4 border-b border-gray-300">
                                                    <nav class="-mb-px flex flex-wrap space-x-4" aria-label="Tabs">
                                                        <button @click="tab = 'prompt'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'prompt', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'prompt' }" class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm">
                                                            プロンプト編集
                                                        </button>
                                                        <button @click="tab = 'qa'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'qa', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'qa' }" class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm">
                                                            質問と回答の例
                                                        </button>
                                                        <button @click="tab = 'details'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'details' }" class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm">
                                                            詳細設定
                                                        </button>
                                                        <button @click="tab = 'embed'" :class="{ 'border-indigo-500 text-indigo-600': tab === 'embed', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'embed' }" class="whitespace-nowrap py-2 px-4 border-b-2 font-medium text-sm">
                                                            設置用コード
                                                        </button>
                                                    </nav>
                                                </div>

                                                <form action="{{ route('chatbots.update', $chatbot) }}" method="POST" class="mt-4">
                                                    @csrf
                                                    <div x-show="tab === 'prompt'" class="py-4 space-y-6">
                                                        <div>
                                                            <label for="additional_prompt_{{ $chatbot->id }}" class="block text-sm font-medium text-gray-700">追加プロンプト</label>
                                                            <textarea id="additional_prompt_{{ $chatbot->id }}" name="additional_prompt" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('additional_prompt', $chatbot->additional_prompt) }}</textarea>
                                                        </div>
                                                        <div>
                                                            <label for="profile_{{ $chatbot->id }}" class="block text-sm font-medium text-gray-700">代表者プロフィール</label>
                                                            <textarea id="profile_{{ $chatbot->id }}" name="profile" rows="10" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('profile', $chatbot->profile) }}</textarea>
                                                        </div>
                                                    </div>
                                                    
                                                    <div x-show="tab === 'qa'" class="py-4">
                                                        <label class="block text-sm font-medium text-gray-700">登録済みのQ&Aペア</label>
                                                        <div id="qa_container_{{ $chatbot->id }}" class="mt-2 space-y-4 qa-container">
                                                            @php
                                                                $questions = [];
                                                                $answers = [];
                                                                if (old('questions.'.$chatbot->id)) {
                                                                    $questions = old('questions.'.$chatbot->id);
                                                                    $answers = old('answers.'.$chatbot->id);
                                                                } elseif (!empty($chatbot->qa_example)) {
                                                                    preg_match_all('/質問例\d+:(.*)/u', $chatbot->qa_example, $q_matches);
                                                                    preg_match_all('/回答例\d+:(.*)/u', $chatbot->qa_example, $a_matches);
                                                                    $questions = array_map('trim', $q_matches[1] ?? []);
                                                                    $answers = array_map('trim', $a_matches[1] ?? []);
                                                                }
                                                            @endphp
                                                            @if (!empty($questions))
                                                                @foreach ($questions as $index => $question)
                                                                    <div class="p-4 border rounded-md bg-white qa-pair">
                                                                        <div class="mt-2">
                                                                            <label class="block text-xs text-gray-600">質問</label>
                                                                            <input type="text" name="questions[{{$chatbot->id}}][]" value="{{ $question }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="質問例を入力...">
                                                                        </div>
                                                                        <div class="mt-2">
                                                                            <label class="block text-xs text-gray-600">回答</label>
                                                                            <input type="text" name="answers[{{$chatbot->id}}][]" value="{{ $answers[$index] ?? '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="回答例を入力...">
                                                                        </div>
                                                                        <div class="flex justify-end">
                                                                            <button type="button" class="bg-red-600 mt-4 py-2 px-4 border rounded-md text-sm text-white hover:bg-red-700 remove-qa-button">削除</button>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                        <div class="mt-4">
                                                            <button type="button" class="bg-emerald-600 py-2 px-4 border rounded-md text-sm text-white hover:bg-emerald-700 add-qa-button">
                                                                + 追加
                                                            </button>
                                                        </div>
                                                    </div>
                                                    
                                                    <div x-show="tab === 'details'" class="py-4 space-y-6">
                                                        <div>
                                                            <label for="chatbot_color_{{ $chatbot->id }}" class="block text-sm font-medium text-gray-700">ChatBotカラー指定</label>
                                                            <div class="mt-1">
                                                                <input type="color" id="chatbot_color_{{ $chatbot->id }}" name="chatbot_color" 
                                                                       value="{{ old('chatbot_color', $chatbot->chatbot_color ?? '#4f46e5') }}" 
                                                                       class="p-1 h-10 w-14 block bg-white border border-gray-300 rounded-md cursor-pointer">
                                                            </div>
                                                            <p class="mt-2 text-sm text-gray-500">チャットボットのテーマカラーを選択します。</p>
                                                        </div>
                                                        
                                                        <div>
                                                            <label for="terms_url_{{ $chatbot->id }}" class="block text-sm font-medium text-gray-700">利用規約リンク</label>
                                                            <div class="mt-1">
                                                                <input type="url" id="terms_url_{{ $chatbot->id }}" name="terms_url" 
                                                                       value="{{ old('terms_url', $chatbot->terms_url) }}" 
                                                                       class="block w-full max-w-lg rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                       placeholder="https://example.com/terms">
                                                            </div>
                                                            <p class="mt-2 text-sm text-gray-500">チャットボットに表示する利用規約ページのURLを入力します。</p>
                                                        </div>

                                                        <div class="pt-6 border-t border-gray-200">
                                                            <h4 class="text-md font-semibold text-gray-800">固定フッターリンク</h4>
                                                            <div class="mt-4 space-y-4">
                                                                <div>
                                                                    <label for="fixed_button_text_{{ $chatbot->id }}" class="block text-sm font-medium text-gray-700">固定表示ボタン名</label>
                                                                    <div class="mt-1">
                                                                        <input type="text" id="fixed_button_text_{{ $chatbot->id }}" name="fixed_button_text" 
                                                                               value="{{ old('fixed_button_text', $chatbot->fixed_button_text) }}" 
                                                                               class="block w-full max-w-lg rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                               placeholder="お問い合わせはこちら">
                                                                    </div>
                                                                    <p class="mt-2 text-sm text-gray-500">Botのメッセージ下に常に表示するテキストを入力します。</p>
                                                                </div>
                                                                <div>
                                                                    <label for="fixed_button_url_{{ $chatbot->id }}" class="block text-sm font-medium text-gray-700">リンク先URL</label>
                                                                    <div class="mt-1">
                                                                        <input type="url" id="fixed_button_url_{{ $chatbot->id }}" name="fixed_button_url" 
                                                                               value="{{ old('fixed_button_url', $chatbot->fixed_button_url) }}" 
                                                                               class="block w-full max-w-lg rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                                               placeholder="https://example.com/contact">
                                                                    </div>
                                                                    <p class="mt-2 text-sm text-gray-500">固定表示ボタンをクリックした際の飛び先URLを入力します。</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div x-show="tab === 'embed'" class="py-4">
                                                        <div class="space-y-6">
                                                            {{-- PC版設置位置 --}}
                                                            <div>
                                                                <label class="text-base font-medium text-gray-900">PC版設置位置</label>
                                                                <p class="text-sm text-gray-500">PCで表示した際のチャットアイコンの位置を選択してください。</p>
                                                                <fieldset class="mt-4">
                                                                    <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                                                                        <div class="flex items-center">
                                                                            <input x-model="pc_position" id="pc_pos_left_{{ $chatbot->id }}" name="pc_position" type="radio" value="left" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <label for="pc_pos_left_{{ $chatbot->id }}" class="ml-3 block text-sm font-medium text-gray-700">左下</label>
                                                                        </div>
                                                                        <div class="flex items-center">
                                                                            <input x-model="pc_position" id="pc_pos_right_{{ $chatbot->id }}" name="pc_position" type="radio" value="right" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <label for="pc_pos_right_{{ $chatbot->id }}" class="ml-3 block text-sm font-medium text-gray-700">右下</label>
                                                                        </div>
                                                                        <div class="flex items-center">
                                                                            <input x-model="pc_position" id="pc_pos_banner_{{ $chatbot->id }}" name="pc_position" type="radio" value="banner" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <label for="pc_pos_banner_{{ $chatbot->id }}" class="ml-3 block text-sm font-medium text-gray-700">右端中央バナー</label>
                                                                        </div>
                                                                    </div>
                                                                </fieldset>
                                                            </div>
                                                            <hr class="my-2">
                                                            {{-- SP版設置位置 --}}
                                                            <div>
                                                                <label class="text-base font-medium text-gray-900">スマートフォン版設置位置</label>
                                                                <p class="text-sm text-gray-500">スマートフォンで表示した際のチャットアイコンの位置を選択してください。</p>
                                                                <fieldset class="mt-4">
                                                                    <div class="space-y-4 sm:flex sm:items-center sm:space-y-0 sm:space-x-10">
                                                                        <div class="flex items-center">
                                                                            <input x-model="sp_position" id="sp_pos_left_{{ $chatbot->id }}" name="sp_position" type="radio" value="left" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <label for="sp_pos_left_{{ $chatbot->id }}" class="ml-3 block text-sm font-medium text-gray-700">左下</label>
                                                                        </div>
                                                                        <div class="flex items-center">
                                                                            <input x-model="sp_position" id="sp_pos_right_{{ $chatbot->id }}" name="sp_position" type="radio" value="right" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <label for="sp_pos_right_{{ $chatbot->id }}" class="ml-3 block text-sm font-medium text-gray-700">右下</label>
                                                                        </div>
                                                                        <div class="flex items-center">
                                                                            <input x-model="sp_position" id="sp_pos_banner_{{ $chatbot->id }}" name="sp_position" type="radio" value="banner" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                            <label for="sp_pos_banner_{{ $chatbot->id }}" class="ml-3 block text-sm font-medium text-gray-700">右端中央バナー</label>
                                                                        </div>
                                                                    </div>
                                                                </fieldset>
                                                            </div>
                                                            <hr class="my-2">
                                                            {{-- ブレークポイント --}}
                                                            <div>
                                                                <label for="breakpoint_{{ $chatbot->id }}" class="block text-base font-medium text-gray-900">ブレークポイント</label>
                                                                <p class="text-sm text-gray-500">PC表示とSP表示を切り替える画面幅(px)を入力してください。</p>
                                                                <div class="mt-2">
                                                                    <input x-model.number="breakpoint" type="number" name="breakpoint" id="breakpoint_{{ $chatbot->id }}" class="block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="768">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- 生成コード表示とコピーボタン --}}
                                                        <div class="mt-8 pt-6 border-t border-gray-300">
                                                            <h4 class="text-base font-medium text-gray-900">埋め込み用コード</h4>
                                                            <p class="text-sm text-gray-500 mb-2">以下のコードをWebサイトの `&lt;/body&gt;` タグの直前に貼り付けてください。</p>
                                                            <div class="relative">
                                                                <pre class="p-4 pr-16 bg-gray-800 text-white rounded-md font-mono text-sm overflow-x-auto"><code x-text="generateEmbedCode()"></code></pre>
                                                                <button type="button" @click="copyToClipboard()" class="absolute top-2 right-2 bg-gray-600 hover:bg-gray-500 text-white py-1 px-3 rounded text-xs transition-colors">
                                                                    <span x-show="!copied">コピー</span>
                                                                    <span x-show="copied" class="text-green-400">コピー完了!</span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>


                                                    <div class="mt-8 pt-4 border-t border-gray-300">
                                                        <button type="submit" class="text-white inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            このChatbotの設定を保存
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-4">登録されているChatbotはありません。</p>
                            @endif
                        </div>
                    </div>

                    {{-- ここから「ログ管理」タブの中身 --}}
                    <div x-show="mainTab === 'logs'" class="p-6 text-gray-900"
                         x-data="logDownloader(
                             '{{ route('logs.show') }}', 
                             '{{ csrf_token() }}',
                             '{{ Auth::user()->group_name }}'
                         )"
                         x-init="loadLogData()">
                        
                        <h3 class="font-semibold text-lg">ログのダウンロード</h3>
                        
                        @if (session('error') && session('from') === 'log_download')
                            <div class="mt-4 p-4 bg-red-100 text-red-700 rounded">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="py-4">
                            <template x-if="!groupNameSet">
                                 <p class="text-yellow-600">あなたのアカウントにグループ名が設定されていないため、ログ機能は利用できません。</p>
                            </template>
                            <template x-if="groupNameSet && loading">
                                <p class="text-gray-500">ログデータを読み込み中...</p>
                            </template>
                            <template x-if="groupNameSet && error">
                                <p class="text-red-500" x-text="error"></p>
                            </template>
                            
                            <template x-if="groupNameSet && !loading && !error && loaded">
                                <form action="{{ route('logs.download') }}" method="POST" class="space-y-4 max-w-sm">
                                    @csrf
                                    <div>
                                        <label for="bot_name_log" class="block text-sm font-medium text-gray-700">Bot選択:</label>
                                        <select name="bot_name" id="bot_name_log" x-model="selectedBot" @change="updateYearOptions()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                            <option value="">選択してください</option>
                                            <template x-for="botName in botNames" :key="botName">
                                                <option :value="botName" x-text="botName"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="year_log" class="block text-sm font-medium text-gray-700">年:</label>
                                        <select name="year" id="year_log" x-model="selectedYear" @change="updateMonthOptions()" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" :disabled="!selectedBot">
                                            <option value="">選択してください</option>
                                             <template x-for="year in availableYears" :key="year">
                                                <option :value="year" x-text="year"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="month_log" class="block text-sm font-medium text-gray-700">月:</label>
                                        <select name="month" id="month_log" x-model="selectedMonth" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" :disabled="!selectedYear">
                                            <option value="">選択してください</option>
                                            <template x-for="month in availableMonths" :key="month">
                                                <option :value="month" x-text="String(month).padStart(2, '0')"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <button type="submit" class="text-white inline-flex justify-center py-2 px-4 border shadow-sm text-sm font-medium rounded-md bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :disabled="!selectedMonth">
                                            ログを取得してダウンロード
                                        </button>
                                    </div>
                                </form>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<style>
    [x-cloak] { display: none !important; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.chatbot-form').forEach(form => {
        const qaContainer = form.querySelector('.qa-container');
        const addQaButton = form.querySelector('.add-qa-button');
        
        const chatbotId = form.dataset.chatbotId;
        if (!chatbotId) return;

        const qaTemplateHtml = `
            <div class="p-4 border rounded-md bg-white qa-pair">
                <div class="mt-2">
                    <label class="block text-xs text-gray-600">質問</label>
                    <input type="text" name="questions[${chatbotId}][]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="質問例を入力...">
                </div>
                <div class="mt-2">
                    <label class="block text-xs text-gray-600">回答</label>
                    <input type="text" name="answers[${chatbotId}][]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="回答例を入力...">
                </div>
                <div class="flex justify-end">
                    <button type="button" class="bg-red-600 mt-4 py-2 px-4 border rounded-md text-sm text-white hover:bg-red-700 remove-qa-button">削除</button>
                </div>
            </div>`;

        if (addQaButton) {
            addQaButton.addEventListener('click', function () {
                if(qaContainer) {
                    const newQaNode = document.createRange().createContextualFragment(qaTemplateHtml);
                    qaContainer.appendChild(newQaNode);
                }
            });
        }

        if (qaContainer) {
            qaContainer.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-qa-button')) {
                    event.target.closest('.qa-pair').remove();
                }
            });
        }
    });
});

function logDownloader(url, csrfToken, groupName) {
    return {
        loading: false,
        loaded: false,
        error: '',
        botNames: [],
        availableDates: {},
        selectedBot: '',
        selectedYear: '',
        selectedMonth: '',
        groupNameSet: !!groupName,
        loadLogData() {
            if (this.loaded || !this.groupNameSet) return;
            this.loading = true;
            this.error = '';
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'サーバーからデータを取得できませんでした。') });
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                this.botNames = data.bot_names;
                this.availableDates = data.available_dates;
                this.loaded = true;
            })
            .catch(error => {
                this.error = 'エラー: ' + error.message;
            })
            .finally(() => {
                this.loading = false;
            });
        },
        get availableYears() {
            if (!this.selectedBot || !this.availableDates[this.selectedBot]) return [];
            return Object.keys(this.availableDates[this.selectedBot]).sort((a, b) => b - a);
        },
        get availableMonths() {
            if (!this.selectedBot || !this.selectedYear || !this.availableDates[this.selectedBot][this.selectedYear]) return [];
            return this.availableDates[this.selectedBot][this.selectedYear].sort((a, b) => a - b);
        },
        updateYearOptions() {
            this.selectedYear = '';
            this.selectedMonth = '';
        },
        updateMonthOptions() {
            this.selectedMonth = '';
        }
    }
}
</script>
</x-app-layout>
