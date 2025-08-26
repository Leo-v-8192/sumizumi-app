<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;
// ▼▼▼【変更点】Chatbotモデルは不要になったので削除 ▼▼▼
// use App\Models\Chatbot; 
use Exception;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    /**
     * ログダウンロード用のUIで使うデータをAjaxで提供する
     */
    // ▼▼▼【変更点】引数から Chatbot $chatbot を削除 ▼▼▼
    public function show(Request $request)
    {
        try {
            // ▼▼▼【変更点】認証済みユーザー情報を取得 ▼▼▼
            $user = $request->user();
            $logBaseUrl = config('services.log_downloader.base_url');
            // ▼▼▼【変更点】ユーザーのgroup_nameを使用 ▼▼▼
            $groupName = $user->group_name; 

            if (!$logBaseUrl || !$groupName) {
                // ▼▼▼【変更点】ログにuser_idを記録 ▼▼▼
                Log::error('Log Downloader Error: Base URL or Group Name is not configured.', ['user_id' => $user->id]);
                return response()->json(['error' => 'エラー: 基本設定（URLまたはグループ名）が不十分です。'], 500);
            }

            $botNames = $this->getBotNames($logBaseUrl, $groupName);
            
            $availableDates = [];
            foreach ($botNames as $botName) {
                $escapedKey = htmlspecialchars($botName, ENT_QUOTES, 'UTF-8');
                $availableDates[$escapedKey] = $this->getAvailableLogDatesForBot($logBaseUrl, $groupName, $botName);
            }

            return response()->json([
                'bot_names' => $botNames,
                'available_dates' => $availableDates,
            ]);

        } catch (Exception $e) {
            Log::error('Log Downloader Exception in show(): ' . $e->getMessage());
            return response()->json(['error' => 'ログデータの取得中にエラーが発生しました。'], 500);
        }
    }

    /**
     * ログファイルを結合してダウンロードする
     */
    // ▼▼▼【変更点】引数から Chatbot $chatbot を削除 ▼▼▼
    public function download(Request $request)
    {
        $request->validate([
            'bot_name' => 'required|string',
            'year' => 'required|integer',
            'month' => 'required|integer',
        ]);

        try {
            // ▼▼▼【変更点】認証済みユーザー情報を取得 ▼▼▼
            $user = $request->user();
            $logBaseUrl = config('services.log_downloader.base_url');
            // ▼▼▼【変更点】ユーザーのgroup_nameを使用 ▼▼▼
            $groupName = $user->group_name;

            // group_nameが設定されていない場合はエラー
            if (empty($groupName)) {
                return back()->with('error', 'グループ名が設定されていないため、ログをダウンロードできません。');
            }

            // フォームから送られてきた生のBot名をファイル名に使う
            $botNameForFilename = $request->bot_name;
            $botName = htmlspecialchars_decode($botNameForFilename, ENT_QUOTES);
            $year = $request->year;
            $month = $request->month;

            $logFileUrls = $this->getLogFileUrlsForMonth($logBaseUrl, $groupName, $botName, $year, $month);

            if (empty($logFileUrls)) {
                return back()->with('error', '指定された条件のログファイルは見つかりませんでした。');
            }

            // 1. 日本語を含むファイル名を作成
            $outputFilenameUtf8 = "log_{$botNameForFilename}_{$year}_" . sprintf('%02d', $month) . ".csv";

            // 2. 念のため、英数字のみの安全なファイル名も用意（古いブラウザ用）
            $outputFilenameAscii = "log_export_{$year}_" . sprintf('%02d', $month) . ".csv";

            // 3. 日本語ファイル名をURLエンコード
            $encodedFilename = rawurlencode($outputFilenameUtf8);

            // 4. 新旧両方のブラウザに対応したContent-Dispositionヘッダーを組み立てる
            $disposition = sprintf(
                'attachment; filename="%s"; filename*=UTF-8\'\'%s',
                $outputFilenameAscii,
                $encodedFilename
            );

            // 5. 新しく組み立てたヘッダーを使う
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => $disposition,
            ];

            return Response::stream(function () use ($logFileUrls) {
                $outputHandle = fopen('php://output', 'w');
                $isFirstFile = true;

                foreach ($logFileUrls as $logUrl) {
                    $response = Http::timeout(20)->get($logUrl);
                    if (!$response->successful()) continue;

                    $csvContent = $response->body();
                    if (substr($csvContent, 0, 3) === "\xEF\xBB\xBF") {
                        $csvContent = substr($csvContent, 3);
                    }
                    $csvContent = str_replace(["\r\n", "\r"], "\n", $csvContent);
                    $lines = explode("\n", $csvContent);

                    if (!$isFirstFile && !empty($lines)) {
                        array_shift($lines);
                    }

                    foreach ($lines as $line) {
                        if (trim($line) !== '') {
                            fwrite($outputHandle, $line . "\n");
                        }
                    }
                    $isFirstFile = false;
                }
                fclose($outputHandle);
            }, 200, $headers);

        } catch (Exception $e) {
            Log::error('Log Downloader Exception in download(): ' . $e->getMessage());
            return back()->with('error', 'ログの処理中にエラーが発生しました。');
        }
    }

    // ===== ヘルパーメソッド群 (変更なし) =====

    private function fetchUrlContent($url)
    {
        $response = Http::timeout(20)->get($url);
        if ($response->failed()) {
            if ($response->status() == 404 || $response->status() == 403) {
                return false;
            }
            $response->throw();
        }
        return $response->body();
    }

    private function getBotNames($baseUrl, $groupName)
    {
        $groupUrl = rtrim($baseUrl, '/') . '/' . rawurlencode($groupName) . '/';
        $html = $this->fetchUrlContent($groupUrl);
        if ($html === false) return [];

        preg_match_all('/<a href="([^"]+?\/)">/i', $html, $matches);
        $botNames = [];
        if (!empty($matches[1])) {
            foreach ($matches[1] as $href) {
                $dirName = rtrim($href, '/');
                $decoded = urldecode($dirName);
                if ($decoded !== '..' && $decoded !== '.' && !empty($decoded) && strpos($decoded, '?') === false && strpos($decoded, '/') === false) {
                    $botNames[] = html_entity_decode($decoded, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }
        }
        return array_unique($botNames);
    }

    private function getAvailableLogDatesForBot($baseUrl, $groupName, $botName)
    {
        $dates = [];
        $botUrl = rtrim($baseUrl, '/') . '/' . rawurlencode($groupName) . '/' . rawurlencode($botName) . '/';
        $yearHtml = $this->fetchUrlContent($botUrl);
        if ($yearHtml === false) return $dates;

        preg_match_all('/<a href="(\d{4})\/">/i', $yearHtml, $yearMatches);
        if (empty($yearMatches[1])) return $dates;

        foreach (array_unique($yearMatches[1]) as $year) {
            $monthUrl = $botUrl . $year . '/';
            $monthHtml = $this->fetchUrlContent($monthUrl);
            if ($monthHtml === false) continue;

            preg_match_all('/<a href="(\d{1,2})\/">/i', $monthHtml, $monthMatches);
            if (empty($monthMatches[1])) continue;

            foreach (array_unique($monthMatches[1]) as $month) {
                $dates[intval($year)][] = intval($month);
            }
            if(isset($dates[intval($year)])) sort($dates[intval($year)]);
        }
        return $dates;
    }

    private function getLogFileUrlsForMonth($baseUrl, $groupName, $botName, $year, $month)
    {
        $urls = [];
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dailyLogUrl = rtrim($baseUrl, '/') . '/' . rawurlencode($groupName) . '/' . rawurlencode($botName) . '/' . sprintf('%04d/%02d/%02d/', $year, $month, $day);
            $html = $this->fetchUrlContent($dailyLogUrl);
            if ($html === false) continue;

            preg_match_all('/<a href="([a-zA-Z0-9_.-]+\.csv)">/i', $html, $matches);
            if (!empty($matches[1])) {
                foreach (array_unique($matches[1]) as $filename) {
                    if (strpos($filename, '..') === false) {
                        $urls[] = $dailyLogUrl . $filename;
                    }
                }
            }
        }
        return $urls;
    }
}
