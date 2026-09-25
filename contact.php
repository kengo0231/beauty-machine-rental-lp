<?php
/**
 * 美容マシン「月額0円レンタル」導入サロン募集LP 資料請求フォーム 受信エンドポイント
 * - 差出人     : info.ohako@gspdn.com（要確認：本案件用のアドレスがあれば差し替え）
 * - 管理者通知 : yokota.fuka@big-youth.com / akita.yasufumi@big-youth.com（要確認）
 * - 自動返信   : 請求者のメールアドレスへ送信（資料URL・デモ予約URLはプレースホルダー）
 * - 完了後     : thanks.html へリダイレクト
 *
 * ■ 設置サーバーが未定のため、$envelope_from は設置ドメインに合わせて必ず見直すこと。
 *   （SPF判定はこのドメインで行われる。不一致だと迷惑メール判定される）
 */

declare(strict_types=1);

mb_language('Japanese');
mb_internal_encoding('UTF-8');

// --- 基本ガード ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// 同一オリジンチェック（外部からの自動投稿をざっくり弾く）
$host    = $_SERVER['HTTP_HOST']    ?? '';
$referer = $_SERVER['HTTP_REFERER'] ?? '';
/* Referer を送らないブラウザが実在する（Meta/Instagram等のアプリ内ブラウザ、
   プライバシー設定、Referrer-Policy: no-referrer 等）。
   以前は「Referer が空」も弾いていたため、正規のリードをエラー画面で失っていた。
   → 別ホストからの投稿だけを拒否し、Referer 不在は通す。 */
if ($referer !== '' && $host !== '' && strpos($referer, $host) === false) {
    log_submission('referer_reject', []);
    error_out('不正なリクエストです。');
}

// ハニーポット（bot対策）。人間は触らない隠しフィールドに値が入っていたら破棄。
/* 旧実装はフィールド名が "website" だったため、ブラウザやパスワード管理ツールの
   自動入力で値が入り、正規のリードを bot と誤判定して破棄する恐れがあった。
   自動入力の対象になりにくい名前に変更し、発動時は必ず記録して追跡できるようにする。 */
if (!empty($_POST['x_verify'] ?? '')) {
    log_submission('honeypot', [
        'name'  => (string)($_POST['name'] ?? ''),
        'email' => (string)($_POST['email'] ?? ''),
        'tel'   => (string)($_POST['tel'] ?? ''),
    ]);
    header('Location: thanks.html'); // botには成功を装う
    exit;
}

// --- 入力取得 -----------------------------------------------------------
function pick(string $k): string {
    $v = $_POST[$k] ?? '';
    if (is_array($v)) $v = implode(', ', $v);
    return trim((string)$v);
}

$salon    = pick('salon');
$name     = pick('name');
$email    = pick('email');
$tel      = pick('tel');
$machine  = pick('machine');
$shoptype = pick('shoptype');
$agree    = pick('agree');

// 選択肢の表示名マッピング
$machine_labels = [
    'A' => '小顔ケア(最新フェイスマシン)',
    'B' => '痩身エステ(FREEZEWAVE)',
    'C' => '両方',
    'D' => '未定',
];
$shoptype_labels = [
    'A' => 'エステサロン',
    'B' => '美容室',
    'C' => '整体・リラクゼーション',
    'D' => 'その他',
];
$machine_label  = $machine_labels[$machine]   ?? ($machine  !== '' ? $machine  : '未選択');
$shoptype_label = $shoptype_labels[$shoptype] ?? ($shoptype !== '' ? $shoptype : '未選択');

// --- バリデーション -----------------------------------------------------
$errors = [];
if ($salon    === '') $errors[] = 'サロン名';
if ($name     === '') $errors[] = 'ご担当者名';
if ($email    === '') $errors[] = 'メールアドレス';
if ($tel      === '') $errors[] = '電話番号';
if ($machine  === '') $errors[] = '導入を検討しているメニュー';
if ($shoptype === '') $errors[] = '店舗の形態';
if ($agree    === '') $errors[] = '個人情報の取り扱いへの同意';
if ($errors) {
    error_out('恐れ入りますが、以下の必須項目をご確認ください。<br>' . htmlspecialchars(implode('、', $errors), ENT_QUOTES, 'UTF-8'));
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    error_out('メールアドレスの形式が正しくないようです。<br>恐れ入りますが、入力内容をご確認ください。');
}

// --- メール共通設定 -----------------------------------------------------
// 管理者通知の宛先（クライアント指定）
$admin_recipients = 'yokota.fuka@big-youth.com, akita.yasufumi@big-youth.com';

/* 差出人（クライアント指定）。①お客様への自動返信・②社内通知の両方で使用する。 */
$from_addr = 'info.ohako@gspdn.com';
$from_name = '美容マシン0円レンタル 資料請求フォーム';
$site_name = '美容マシン 導入サロン募集';

/* エンベロープ送信元（Return-Path）。
   SPF判定はこのアドレスのドメインに対して行われるため、実際に送信する
   サーバー(sv16710.xserver.jp)と一致する設置ドメインを指定して認証を通す。
   ※gspdn.com にはSPF/DKIMが未設定のため、$from_addr のドメインで
     SPF判定させると「認証なし」となり迷惑メール判定されやすい。
     恒久対応は gspdn.com のDNSに下記TXTを追加すること：
     v=spf1 include:_spf.google.com include:spf.sender.xserver.jp ~all */
$envelope_from = 'info@ohako-beautyresort.com';

// 請求者からの返信を受けるアドレス（特商法表記の記載と同一）
$contact_addr = 'info.ohako@gspdn.com';

/* 自動返信メールに載せるリンク。差し替えるときはここだけ変更する。
   ※資料URLは「リンクを知っている全員が閲覧可」の共有設定であることが前提。 */
$doc_url     = '【ここに導入資料のURLを入れる】';              // ★要設定
$booking_url = 'https://timerex.net/s/0003377/b2ce438a'; // TimeRex（2026-09-25 オーナー指定）

/* ===== スプレッドシート連携（サービスアカウント方式） ==================
   Google Sheets API に直接書き込む。サービスアカウントはGoogleアカウントとは
   独立した「プログラム専用のアカウント」なので、OAuthの承認画面が出ず、
   Workspaceの「このアプリはブロックされます」制限を受けない。

   格納先:
   https://docs.google.com/spreadsheets/d/1FOmfQ20Ze8hlxRWc9EyktPXu61-qXGjpF1d6vnAsfwM/
   （gid=1971112648 のタブ）

   ※未設定でもフォーム自体は正常に動作する。その場合は
     submissions-backup.csv に自動保存されるので取りこぼしはない。
   ==================================================================== */
/* ★要設定: 本案件用のスプレッドシートID/gid。空の間は submissions-backup.csv に退避される。 */
$sheet_id      = '';
$sheet_gid     = 0;
// サービスアカウントの鍵ファイル（contact.php と同じ階層に置く）
$sa_key_file   = __DIR__ . '/service-account-key.json';

$received_at = (new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo')))
    ->format('Y-m-d H:i:s');

/* --- 多重送信のガード -------------------------------------------------
   送信ボタンの二度押し等で同一内容が短時間に再送された場合、
   メールもシート記録もスキップしてサンクスページへ進める。
   ユーザーから見た挙動は正常な送信と変わらない。 */
$fingerprint = hash('sha256', mb_strtolower($email) . '|' . preg_replace('/\D/', '', $tel) . '|' . $name);
if (is_duplicate_submission($fingerprint)) {
    error_log('[beauty-machine-rental-lp] duplicate submission skipped: ' . $email);
    log_submission('duplicate', compact('salon','name','email','tel') + ['machine'=>$machine_label,'shoptype'=>$shoptype_label]);
    header('Location: thanks.html');
    exit;
}

/**
 * 多重送信の判定。
 * 送信ボタンの二度押し・回線の遅延による再送などで、同一内容が
 * 短時間に複数回POSTされるのを防ぐ。
 *
 * 同じ内容が $window 秒以内に再度来た場合は true を返し、
 * 呼び出し元でメール送信・シート記録をスキップする。
 *
 * @param string $fingerprint 送信内容から作った識別子
 * @param int    $window      重複とみなす秒数
 */
function is_duplicate_submission(string $fingerprint, int $window = 600): bool {
    $dir = __DIR__ . '/.dedupe';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir)) {
        return false; // 作れない環境では重複判定をあきらめる（送信は通す）
    }

    // 古い記録を掃除（100回に1回程度）
    if (mt_rand(1, 100) === 1) {
        foreach ((array)glob($dir . '/*.log') as $old) {
            if (is_file($old) && (time() - (int)filemtime($old)) > 86400) @unlink($old);
        }
    }

    $file = $dir . '/' . $fingerprint . '.log';
    $fh   = @fopen($file, 'c+');
    if (!$fh) return false;

    // 同時POSTでもどちらか一方だけが通るように排他ロックをかける
    if (!flock($fh, LOCK_EX)) { fclose($fh); return false; }

    $last = (int)trim((string)fread($fh, 32));
    $now  = time();

    if ($last > 0 && ($now - $last) < $window) {
        flock($fh, LOCK_UN);
        fclose($fh);
        return true;
    }

    ftruncate($fh, 0);
    rewind($fh);
    fwrite($fh, (string)$now);
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return false;
}

/**
 * すべての送信を「結果つき」で記録する（取りこぼしの追跡用）。
 * outcome: ok / duplicate / honeypot / referer_reject / sheet_failed
 * ※個人情報を含むため .htaccess で外部アクセスを遮断している。
 */
function log_submission(string $outcome, array $d): void {
    $path = __DIR__ . '/submissions-log.csv';
    $new  = !file_exists($path) || filesize($path) === 0;
    $fh = @fopen($path, 'a');
    if (!$fh) return;
    if (flock($fh, LOCK_EX)) {
        if ($new) {
            fwrite($fh, "\xEF\xBB\xBF"); // ExcelでのUTF-8文字化け防止
            fputcsv($fh, ['記録日時','結果','サロン名','ご担当者名','メールアドレス','電話番号','導入希望機種','店舗形態','UA']);
        }
        fputcsv($fh, [
            date('Y-m-d H:i:s'),
            $outcome,
            $d['salon']    ?? '',
            $d['name']     ?? '',
            $d['email']    ?? '',
            $d['tel']      ?? '',
            $d['machine']  ?? '',
            $d['shoptype'] ?? '',
            substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 120),
        ]);
        flock($fh, LOCK_UN);
    }
    fclose($fh);
}

/** base64url エンコード（JWT用） */
function b64url(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

/** 汎用HTTPリクエスト。[HTTPステータス, レスポンス本文] を返す。 */
function http_request(string $url, string $method = 'GET', $body = null, array $headers = []): array {
    $ch = curl_init($url);
    $opt = [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT        => 10,
    ];
    if ($body !== null) $opt[CURLOPT_POSTFIELDS] = $body;
    curl_setopt_array($ch, $opt);
    $res  = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, (string)$res];
}

/**
 * サービスアカウントの鍵で署名し、Sheets API 用のアクセストークンを取得する。
 * OAuthの同意画面を経由しないため、Workspaceのアプリ制限の影響を受けない。
 */
function get_sheets_access_token(array $sa): string {
    if (empty($sa['client_email']) || empty($sa['private_key'])) return '';

    $now = time();
    $jwt_header = b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
    $jwt_claim  = b64url(json_encode([
        'iss'   => $sa['client_email'],
        'scope' => 'https://www.googleapis.com/auth/spreadsheets',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now,
    ]));

    $signature = '';
    if (!openssl_sign("{$jwt_header}.{$jwt_claim}", $signature, $sa['private_key'], 'sha256WithRSAEncryption')) {
        return '';
    }
    $jwt = "{$jwt_header}.{$jwt_claim}." . b64url($signature);

    [$code, $res] = http_request(
        'https://oauth2.googleapis.com/token',
        'POST',
        http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]),
        ['Content-Type: application/x-www-form-urlencoded']
    );
    if ($code !== 200) {
        error_log('[beauty-machine-rental-lp] token request failed: ' . $res);
        return '';
    }
    $json = json_decode($res, true);
    return $json['access_token'] ?? '';
}

/** gid からタブ名を引く（タブ名を変更されても壊れないようにするため） */
function get_sheet_title(string $sheet_id, int $gid, string $token): string {
    $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheet_id}?fields=sheets.properties";
    [$code, $res] = http_request($url, 'GET', null, ["Authorization: Bearer {$token}"]);
    if ($code !== 200) {
        error_log('[beauty-machine-rental-lp] sheet meta failed: ' . $res);
        return '';
    }
    foreach ((json_decode($res, true)['sheets'] ?? []) as $s) {
        if ((int)($s['properties']['sheetId'] ?? -1) === $gid) {
            return (string)($s['properties']['title'] ?? '');
        }
    }
    return '';
}

/**
 * スプレッドシートの指定タブに1行追記する。
 * 失敗してもフォームの動作は止めない（メール送信とサンクスページを優先）。
 */
function append_to_sheet(string $key_file, string $sheet_id, int $gid, array $row, array $headers): bool {
    if (!is_readable($key_file) || !function_exists('curl_init') || !function_exists('openssl_sign')) {
        return false;
    }
    $sa = json_decode((string)file_get_contents($key_file), true);
    if (!is_array($sa)) return false;

    $token = get_sheets_access_token($sa);
    if ($token === '') return false;

    $title = get_sheet_title($sheet_id, $gid, $token);
    if ($title === '') return false;

    $range = rawurlencode($title) . '!A:' . chr(64 + count($headers));
    $url   = "https://sheets.googleapis.com/v4/spreadsheets/{$sheet_id}/values/{$range}:append"
           . '?valueInputOption=USER_ENTERED&insertDataOption=INSERT_ROWS';

    // 1件目のときは見出し行から入れる
    $values = [];
    [$c, $r] = http_request(
        "https://sheets.googleapis.com/v4/spreadsheets/{$sheet_id}/values/" . rawurlencode($title) . '!A1:A1',
        'GET', null, ["Authorization: Bearer {$token}"]
    );
    if ($c === 200 && empty(json_decode($r, true)['values'])) {
        $values[] = $headers;
    }
    $values[] = $row;

    [$code, $res] = http_request($url, 'POST', json_encode(['values' => $values]), [
        "Authorization: Bearer {$token}",
        'Content-Type: application/json',
    ]);
    if ($code !== 200) {
        error_log('[beauty-machine-rental-lp] sheet append failed: ' . $res);
        return false;
    }
    return true;
}

/**
 * シート送信が失敗したときの保険。サーバー上にCSVで控えを残す。
 * ※.htaccess で外部からの直接アクセスを禁止している。
 */
function append_local_backup(array $row): void {
    $path = __DIR__ . '/submissions-backup.csv';
    $fh = @fopen($path, 'a');
    if (!$fh) return;
    if (filesize($path) === 0) {
        fwrite($fh, "\xEF\xBB\xBF"); // ExcelでのUTF-8文字化け防止
        fputcsv($fh, ['日時', 'サロン名', 'ご担当者名', 'メールアドレス', '電話番号', '導入希望機種', '店舗形態']);
    }
    fputcsv($fh, $row);
    fclose($fh);
}

// ヘッダ組み立て（mb_send_mail 用）
function build_headers(string $from_name, string $from_addr, ?string $reply_to = null): string {
    $from_enc = mb_encode_mimeheader($from_name, 'ISO-2022-JP-MS');
    $h  = "From: {$from_enc} <{$from_addr}>\r\n";
    if ($reply_to) $h .= "Reply-To: {$reply_to}\r\n";
    $h .= "X-Mailer: PHP/" . phpversion();
    return $h;
}

// --- 管理者通知メール ---------------------------------------------------
$admin_subject = "【美容マシン0円レンタルLP】資料請求 / {$salon} {$name} 様（{$machine_label}）";
$admin_body = <<<EOT
━━━━━━━━━━━━━━━━━━━━━━━━━━━
■ 美容マシン導入サロン募集LPから資料請求がありました
━━━━━━━━━━━━━━━━━━━━━━━━━━━
受信日時        : {$received_at}

【サロン情報】
サロン名        : {$salon}
ご担当者名      : {$name}
メールアドレス  : {$email}
電話番号        : {$tel}

【導入を検討している機種】
{$machine_label}

【店舗の形態】
{$shoptype_label}

━━━━━━━━━━━━━━━━━━━━━━━━━━━
※請求者へは自動返信メールを送信済みです。
　資料の送付とデモ・ご相談の日程調整をお願いいたします。

　本メールにそのまま返信すると、請求者ご本人に届きます。
EOT;

$admin_headers = build_headers($from_name, $from_addr, $email);
$sent_admin = @mb_send_mail($admin_recipients, $admin_subject, $admin_body, $admin_headers, "-f{$envelope_from}");
if (!$sent_admin) {
    error_log('[beauty-machine-rental-lp] admin mail send failed for: ' . $email);
}

// --- 請求者への自動返信メール -------------------------------------------
$reply_subject = '【美容マシン0円レンタル】資料請求を承りました';
$reply_body = <<<EOT
{$name} 様

このたびは、美容マシン「月額レンタル料0円」導入システムに
ご興味をお持ちいただき、誠にありがとうございます。

導入資料をご用意しました。下記よりご覧ください。

──────────────────────────────
■ 導入資料はこちら
──────────────────────────────
　{$doc_url}

　【掲載内容】
　1. 導入条件 … 初期導入費・機器使用料・契約条件
　2. 2機種の詳細 … 最新フェイスマシン / FREEZEWAVE
　3. メニュー設計例と収支シミュレーション

──────────────────────────────
■ デモ・ご相談のご予約はこちら
──────────────────────────────
　{$booking_url}

　機器の体感と、貴サロンでのメニュー設計を個別にご相談いただけます。
　強引な勧誘はいたしませんので、情報収集の場としてご活用ください。

■ お問い合わせ
　本メールへのご返信、または {$contact_addr} までご連絡ください。

※掲載金額は料金設定例によるシミュレーションであり、
　売上・利益を保証するものではありません。
※機器使用料以外に初期導入費等が必要です。

===========================================
株式会社GIANT SWING PRODUCTIONS
〒105-0003 東京都港区西新橋3-13-7 VORT虎ノ門SOUTH 9F
TEL：03-5843-1021
MAIL：{$contact_addr}
※本メールは自動送信ですが、ご返信いただけます。
EOT;

// 差出人が info.ohako@gspdn.com なので、返信はそのまま同アドレスに届く
$reply_headers = build_headers($site_name, $from_addr, $contact_addr);
@mb_send_mail($email, $reply_subject, $reply_body, $reply_headers, "-f{$envelope_from}");

// --- スプレッドシートへ記録 ---------------------------------------------
$sheet_row     = [$received_at, $salon, $name, $email, $tel, $machine_label, $shoptype_label];
$sheet_headers = ['日時', 'サロン名', 'ご担当者名', 'メールアドレス', '電話番号', '導入希望機種', '店舗形態'];

$sheet_ok = $sheet_id !== ''
    ? append_to_sheet($sa_key_file, $sheet_id, $sheet_gid, $sheet_row, $sheet_headers)
    : false;

$log_row = compact('salon','name','email','tel') + ['machine'=>$machine_label,'shoptype'=>$shoptype_label];
if (!$sheet_ok) {
    // シートに入らなかった分は必ずサーバー側に残す（リードの取りこぼし防止）
    error_log('[beauty-machine-rental-lp] sheet append failed for: ' . $email);
    append_local_backup($sheet_row);
    log_submission('sheet_failed', $log_row);
} else {
    log_submission('ok', $log_row);
}

// --- 完了 ---------------------------------------------------------------
header('Location: thanks.html');
exit;


/**
 * 入力エラー時に簡易エラーページを表示して終了する。
 */
function error_out(string $message): void {
    http_response_code(400);
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NTWC288X');</script>
<!-- End Google Tag Manager -->
<meta name="robots" content="noindex, nofollow">
<title>入力内容のご確認 | 美容マシン 導入サロン募集</title>
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Noto+Serif+JP:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body class="page-body">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NTWC288X"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
<main class="page-main">
  <div class="container">
    <h1 class="thanks__title">入力内容をご確認ください</h1>
    <p class="thanks__lead">{$message}</p>
    <a href="javascript:history.back()" class="cta-btn">入力画面に戻る</a>
  </div>
</main>
</body>
</html>
HTML;
    exit;
}
