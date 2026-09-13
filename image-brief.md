# 画像生成 指示書(ChatGPT 画像生成用)

- 対象LP: 美容マシン「月額レンタル料0円」導入サロン募集LP(リニューアル版)
- 作成日: 2026-09-14
- 用途: 新デザイン(白×ブラッシュピンク×ローズ。FREEZEWAVEパートのみアイスブルー)に入れる実写風イメージ写真
- 生成枚数: 全9枚(必須7枚 + 任意2枚)。各2〜3案生成して、いちばん自然なものを採用してください

---

## 1. 全画像に共通するルール(必ずプロンプト末尾に付ける)

### 共通スタイル(日本語)
- 実写風の写真。明るい自然光、白基調に淡いピンク/ベージュ。清潔感・高級感のある日本の美容サロン
- モデルは日本人女性(20〜40代)。過度なレタッチ・非現実的な肌質にしない
- 画像内に文字・数字・ロゴ・透かしを入れない
- **美容機器・マシン・パッド・ハンドピースを一切映さない**(製品はメーカー実写を使うため、AI生成の機械が混ざると不正確になる)
- ビフォーアフター、痩身・小顔の「効果」を連想させる表現(ウエストを測る、お腹をつまむ等)は入れない
- 肌の露出は控えめ(腕・脚・デコルテまで。タオル・ガウン着用)。広告審査(Meta)に通る清潔な表現
- 余白を多めに取り、被写体は指定した位置に置く(LP上でテキストを重ねる/角丸にトリミングするため)

### 共通プロンプト末尾(英語・コピペ用)
```
Photorealistic editorial photo, soft natural window light, bright and airy, white and pale blush pink tones, clean modern Japanese beauty salon, Japanese woman model, realistic skin texture, no text, no letters, no numbers, no logo, no watermark, no beauty devices or machines, no medical tools, no before-after, modest and tasteful, high resolution.
```

### サイズ指定
| 指定 | 生成サイズ | 用途 |
|---|---|---|
| 横長 | 1536×1024 | PC表示・横長カード |
| 縦長 | 1024×1536 | スマホFV |
| 正方形 | 1024×1024 | セクション内の角丸写真 |

### 納品
- 形式: PNG または JPG(生成サイズのまま。圧縮・トリミングはこちらで行います)
- ファイル名: 下記の指定名で保存 → LPフォルダの `img/` に入れるか、共有してください

---

## 2. 生成する画像一覧

| # | ファイル名 | サイズ | 掲載箇所 | 必須 |
|---|---|---|---|---|
| 1 | `fv-hero-pc.jpg` | 横長 | ファーストビュー(PC) | 必須 |
| 2 | `fv-hero-sp.jpg` | 縦長 | ファーストビュー(スマホ) | 必須 |
| 3 | `merit-simul.jpg` | 横長 | 「いつもの施術と、同時に。」(同時施術の説明) | 必須 |
| 4 | `merit-two-clients.jpg` | 横長 | 「スタッフ1人で、2名同時に。」 | 必須 |
| 5 | `owner-worry.jpg` | 正方形 | 「新しい美容マシンを導入したい、でも…」(悩み) | 必須 |
| 6 | `face-care.jpg` | 正方形 | 機種① 最新フェイスマシン(フェイスケアの雰囲気) | 必須 |
| 7 | `body-care.jpg` | 正方形 | 機種② FREEZEWAVE(ボディケアの雰囲気・寒色トーン) | 必須 |
| 8 | `consult.jpg` | 横長 | 導入の流れ(ご相談・デモ)/ 資料請求フォーム上 | 任意 |
| 9 | `bg-texture.jpg` | 横長 | FV・セクションの背景テクスチャ | 任意 |

※図解(同時施術のタイムライン、月額固定vs使った分だけの棒グラフ、顔・ボディの部位図)とアイコンは、LP側でコーディング(SVG)して作るため生成不要です。
※製品写真(フェイスマシン本体・パッド・操作画面 / FREEZEWAVE本体・ハンドピース)はメーカーのマニュアルから取り込み済みです。

---

## 3. 各画像のプロンプト

### 1. `fv-hero-pc.jpg`(横長 1536×1024)
**内容**: 明るい施術室。施術ベッドに仰向けで横たわり、目を閉じてリラックスしている女性。白いタオルとガウン。顔には何も付けていない。
**構図**: 被写体は画面の右1/3。左2/3はやわらかくボケた白〜淡ピンクの空間(ここに見出しと「0円」の数字を重ねる)。カメラはやや斜め上から。
```
A Japanese woman in her 30s lying on a treatment bed with her eyes closed, relaxed and peaceful, wearing a white towel wrap, nothing on her face. Bright modern beauty salon treatment room. The woman is placed in the right third of the frame; the left two-thirds is a soft, out-of-focus white and pale pink space with lots of empty room for text. Shot from a slightly elevated angle, shallow depth of field, 3:2 landscape.
```
+ 共通プロンプト末尾

### 2. `fv-hero-sp.jpg`(縦長 1024×1536)
**内容**: 1と同じシーン・同じ雰囲気の縦位置。
**構図**: 被写体は画面の下半分。上半分は淡いピンク〜白の静かな空間(ここに見出しを重ねる)。
```
Same scene and mood: a Japanese woman in her 30s lying on a treatment bed with her eyes closed, relaxed, white towel wrap, nothing on her face, bright modern beauty salon. Vertical composition: the woman occupies the lower half of the frame; the upper half is a calm, soft-focus white and pale pink area with empty space for text. 2:3 portrait.
```
+ 共通プロンプト末尾

### 3. `merit-simul.jpg`(横長 1536×1024)
**内容**: セラピストがお客様の脚(ふくらはぎ〜太もも、タオル越し)をハンドトリートメントしている。お客様は仰向けで目を閉じてリラックス。顔は空いている(=顔はセルフマシンに任せられる、を文章で説明するため、顔に器具は付けない)。
**構図**: ベッドを横から。お客様の顔が画面左、セラピストの手元が画面右。
```
A female Japanese esthetician in a clean white uniform giving a gentle hand massage to a client's leg over a white towel. The client, a Japanese woman in her 30s, lies on her back with eyes closed, calm and relaxed, face uncovered with nothing on it. Side view of the treatment bed: the client's face on the left of the frame, the therapist's hands on the right. Bright, airy salon room, white and pale pink tones, 3:2 landscape.
```
+ 共通プロンプト末尾

### 4. `merit-two-clients.jpg`(横長 1536×1024)
**内容**: 施術ベッドが2台並ぶ明るい部屋。手前のベッドではお客様が一人で目を閉じてリラックス(スタッフは付いていない)。奥のベッドではセラピストが別のお客様を施術中。「スタッフ1人で2名を同時に」が伝わる絵。
```
A bright beauty salon room with two treatment beds side by side. On the front bed, a Japanese woman in her 30s lies alone with her eyes closed, relaxed, wearing a white towel wrap, no staff beside her. On the back bed, a female esthetician in a white uniform is giving a gentle hand treatment to another female client. Only one esthetician in the scene. Soft natural light, white and pale pink tones, wide shot, 3:2 landscape.
```
+ 共通プロンプト末尾

### 5. `owner-worry.jpg`(正方形 1024×1024)
**内容**: サロンオーナーの女性(30代後半〜40代)。受付カウンターでノートPCや書類を前に、少し考え込む・悩む表情(深刻すぎない)。白いユニフォームまたは清潔なシャツ。
```
A Japanese female beauty salon owner in her late 30s, standing at a bright salon reception counter with a laptop and a few papers, looking slightly worried and thoughtful, hand lightly touching her chin. Clean white uniform or a neat white shirt. Bright, airy salon interior with pale pink accents, medium shot, 1:1 square.
```
+ 共通プロンプト末尾

### 6. `face-care.jpg`(正方形 1024×1024)
**内容**: フェイスケアの雰囲気写真。清潔感のある女性の顔のクローズアップ。目を閉じ、頬に指先を軽く添えている。ヘアバンドで髪を上げ、素肌感のある自然なメイク。
**注意**: 顔に器具・パッド・シートマスクは付けない。
```
Close-up beauty portrait of a Japanese woman in her 30s with her eyes closed, gently touching her cheek with her fingertips, hair pulled back with a soft white headband, natural minimal makeup, healthy realistic skin. Nothing attached to her face. Soft white and pale pink background, natural light, 1:1 square.
```
+ 共通プロンプト末尾

### 7. `body-care.jpg`(正方形 1024×1024)
**内容**: ボディケアの雰囲気写真。うつ伏せで横たわる女性の背中〜腰にセラピストの手が添えられている。白いタオルで腰から下を覆う。**このパートはアイスブルー系のデザインなので、白×淡い水色のクールで清潔なトーン**にする。
**注意**: お腹をつまむ・ウエストを測る等の痩身表現は不可。
```
A Japanese woman in her 30s lying face down on a treatment bed, lower body covered with a white towel, an esthetician's hands resting gently on her upper back. Calm and relaxed. Cool, clean color palette: white with pale ice blue tones instead of pink, soft natural light, 1:1 square.
```
+ 共通プロンプト末尾(※この画像だけ「pale blush pink」を「pale ice blue」に置き換える)

### 8. `consult.jpg`(横長 1536×1024・任意)
**内容**: 明るいサロン内で、担当者がサロンオーナーにタブレットを見せながら説明している。2人とも笑顔で前向きな雰囲気。
```
A friendly consultation scene in a bright beauty salon: a sales representative in a neat jacket showing a tablet to a Japanese female salon owner in a white uniform. Both are smiling and engaged, sitting at a small white table. The tablet screen is blank white. Soft natural light, white and pale pink tones, 3:2 landscape.
```
+ 共通プロンプト末尾

### 9. `bg-texture.jpg`(横長 1536×1024・任意)
**内容**: 背景用の抽象テクスチャ。白〜淡いピンクのシルクの布、または水面のやわらかな波紋。被写体なし。
```
Abstract background texture: soft flowing white and pale blush pink silk fabric with gentle folds, very light and airy, subtle gradient, no objects, no people, minimal, 3:2 landscape.
```
+ 共通プロンプト末尾

---

## 4. 生成のコツ

- 1枚目(fv-hero-pc)を先に作り、以降は「前の画像と同じトーン・同じサロンで」と添えると統一感が出ます
- 機械が勝手に描き込まれたら「remove any devices or machines」と修正指示を出してください
- 手の描写が崩れやすいので、手元が主役の3・4・7は複数案を生成して選んでください
- 人物の顔がLP上で小さくなる3・4・8は、顔の細部より全体の明るさ・清潔感を優先してOKです
