# irodori Translate Engine

[日本語はこちらへ](#japanese)

This is a simple PHP wrapper for utilizing the APIs provided by Sakura Internet's "[Sakura AI Engine](https://www.sakura.ad.jp/aipf/ai-engine/)" for translation. It extracts and libraries the core translation processing used in "Irodori Translation".

## Features

- Class-based API design that is simple and intuitive to use.
- Wide support for API translation options, such as language and style settings.
- Easy switching between "natural translation" and "literal translation" modes.
- Supports custom prompts for fine-tuning translation styles.

## Compatibility

This library is designed to work with Sakura Internet's AI Engine's OpenAI-compatible API.
Although not officially tested, it may also work with other compatible APIs.
Please use it at your own risk.

## Requirements

- PHP 7.4 or higher
- cURL PHP extension
- JSON PHP extension

## Installation

Install the library via [Composer](httpss://getcomposer.org/).

```bash
composer require koba9813/irodori-translate-engine
```


## Usage

### Basic Example

This shows how to perform a simple translation. Remember to implement exception handling for robust error management.

```php
<?php

require 'vendor/autoload.php';

use Irodori\Honyaku\Translator;

// It is strongly recommended to load the API key from environment variables.
$apiKey = getenv('SAKURA_API_KEY');
if (!$apiKey) {
    die('API key is not set!');
}

try {
    // 1. Instantiate the Translator
    $translator = new Translator($apiKey);

    // 2. Set translation options
    $options = [
        'text' => 'こんにちは、世界！',
        'target' => 'english', // Target language
        'source' => 'japanese'  // Source language ('auto' is also available)
    ];

    // 3. Execute the translation
    $result = $translator->translate($options);

    // 4. Display the result
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    // Handle input errors (e.g., empty text)
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request: ' . $e->getMessage()]);
} catch (RuntimeException $e) {
    // Handle API communication or response parsing errors
    http_response_code(503);
    echo json_encode(['error' => 'Service unavailable: ' . $e->getMessage()]);
} catch (Throwable $e) {
    // Handle other unexpected errors
    http_response_code(500);
    echo json_encode(['error' => 'An internal server error occurred: ' . $e->getMessage()]);
}

```

### Available Options

The following keys can be used in the `$options` array passed to the `translate()` method.

| Key             | Type    | Default     | Description                                                                                                                                                              |
|-----------------|---------|-------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `text`          | string  | **Required**| Text to translate. Maximum 1500 characters.                                                                                                                              |
| `target`        | string  | `'english'` | Target language ID. You can specify one of the following: `'japanese'`, `'english'`, `'french'`, `'korean'`, `'chinese'`.                                              |
| `source`        | string  | `'auto'`    | Source language ID. If `'auto'` is specified, the language will be detected automatically. The same language IDs as specified for `target` can be used.                                                                              |
| `is_literal`    | boolean | `false`     | If `true`, performs a strict literal translation. The default is a more natural translation (free translation).                                                               |
| `style`         | string  | `'standard'`| Specifies the translation style. Available styles vary depending on the target language and the value of `target`. If `'custom'` is specified, `custom_prompt` will be used.<br>- **Japanese**: `'casual'` (casual expression/informal), `'polite'` (polite expression/formal), `'academic'` (academic expression/da-dearu style), `'kansai'` (Kansai dialect)<br>- **English**: `'american'` (American English spelling and idioms), `'british'` (British English spelling and idioms), `'middle_school'` (plain English understandable by Japanese junior high school students)<br>- **Chinese**: `'simplified'` (Simplified Chinese), `'traditional'` (Traditional Chinese)<br>- Other languages: Currently only `'standard'` is supported. |
| `custom_prompt` | string  | `''`        | Custom instructions for translation, used only when `style` is set to `'custom'`. Overrides other style settings.                                                        |

## License

This project is released under the MIT License. Please see the [LICENSE](LICENSE) file for details.

-------------------------------------------------------------------------------------------

<a id="japanese"></a>
# irodori Translate Engine

さくらインターネットの「[さくらのAI Engine](https://www.sakura.ad.jp/aipf/ai-engine/)」で提供されているAPIを翻訳に利用するための、シンプルなPHPラッパーです。「Irodori翻訳」で実際に使われている翻訳処理のコア部分を切り出してライブラリ化しています。

## 特徴

- シンプルで直感的に使える、クラスベースのAPI設計
- 言語指定やスタイル設定など、APIの翻訳オプションを幅広くサポート
- 「自然な翻訳」と「直訳」モードの簡単な切り替え。
- 翻訳スタイルを細かく調整できるカスタムプロンプトに対応

## 互換性

このライブラリは、さくらインターネットのAI EngineのOpenAI互換APIで動くように作っています。
公式に動作確認はしていませんが、ほかの互換APIでも動くかもしれません。
使うときは自己責任でお願いします。

## 要件

- PHP 7.4 以上
- cURL PHP拡張機能
- JSON PHP拡張機能

## インストール

ライブラリを [Composer](httpss://getcomposer.org/) 経由でインストールします。

```bash
composer require koba9813/irodori-translate-engine
```


## 使い方

### 基本的な例

簡単な翻訳を実行する方法です。堅牢なエラー管理のために、例外処理を忘れないようにしてください。

```php
<?php

require 'vendor/autoload.php';

use Irodori\Honyaku\Translator;

// 環境変数からAPIキーを読み込むことを強く推奨します。
$apiKey = getenv('SAKURA_API_KEY');
if (!$apiKey) {
    die('APIキーが設定されていません！');
}

try {
    // 1. Translatorをインスタンス化します
    $translator = new Translator($apiKey);

    // 2. 翻訳オプションを設定します
    $options = [
        'text' => 'こんにちは、世界！',
        'target' => 'english', // 翻訳先の言語
        'source' => 'japanese'  // 翻訳元の言語（'auto'も利用可能）
    ];

    // 3. 翻訳を実行します
    $result = $translator->translate($options);

    // 4. 結果を表示します
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    // 入力エラーの処理（例：テキストが空）
    http_response_code(400);
    echo json_encode(['error' => '不正なリクエストです: ' . $e->getMessage()]);
} catch (RuntimeException $e) {
    // API通信やレスポンス解析エラーの処理
    http_response_code(503);
    echo json_encode(['error' => 'サービス利用不可: ' . $e->getMessage()]);
} catch (Throwable $e) {
    // その他の予期せぬエラーの処理
    http_response_code(500);
    echo json_encode(['error' => '内部サーバーエラーが発生しました: ' . $e->getMessage()]);
}

```

### 利用可能なオプション

`translate()` メソッドに渡す `$options` 配列で、以下のキーが使用できます。

| キー            | 型      | デフォルト    | 説明                                                                                                                                     |
|-----------------|---------|-------------|------------------------------------------------------------------------------------------------------------------------------------------|
| `text`          | string  | **必須**    | 翻訳するテキスト。最大1500文字。                                                                                                       |
| `target`        | string  | `'english'` | 翻訳先の言語ID。以下のいずれかを指定できます: `'japanese'`, `'english'`, `'french'`, `'korean'`, `'chinese'`。                          |
| `source`        | string  | `'auto'`    | 翻訳元の言語ID。`'auto'` を指定すると自動で言語を検出します。上記`target`で指定できる言語IDと同じものが使用できます。                                                                           |
| `is_literal`    | boolean | `false`     | `true` の場合、厳密な直訳（直訳）を実行します。デフォルトはより自然な翻訳（意訳）です。                                                                  |
| `style`         | string  | `'standard'`| 翻訳のスタイルを指定します。対象言語と`target`の値によって利用可能なスタイルが異なります。`'custom'`を指定すると`custom_prompt`が使用されます。<br>- **日本語**: `'casual'` (カジュアルな表現/タメ口), `'polite'` (丁寧な表現/敬語), `'academic'` (学術的な表現/だ・である調), `'kansai'` (関西弁) <br>- **英語**: `'american'` (アメリカ英語のスペルと慣用句), `'british'` (イギリス英語のスペルと慣用句), `'middle_school'` (日本の一般的な中学生にも理解できる平易な英語)<br>- **中国語**: `'simplified'` (簡体字), `'traditional'` (繁体字)<br>- その他の言語: 現在は`'standard'`のみサポートされています。                                                                           |
| `custom_prompt` | string  | `''`        | `style`に`'custom'`を指定した場合にのみ使用される、翻訳へのカスタム指示。他のスタイル設定を上書きします。                                             |

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。詳細は [LICENSE](LICENSE) ファイルをご覧ください。
