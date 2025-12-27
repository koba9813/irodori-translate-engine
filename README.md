# irodori Translate Engine

日本語はこちら: [日本語セクションへ](#japanese)

A simple PHP wrapper to use the AI APIs from Sakura Internet's "Sakura AI Engine" for translation. It's the core translation logic extracted and packaged as a library from the actual "Irodori Translation" tool.

## Features

- Simple, intuitive class-based API that's easy to pick up and use
- Full support for translation options like language selection and style tweaks
- Quick toggle between "natural" and "literal" translation modes
- Custom prompts to fine-tune the translation style just how you want it

## Requirements

- PHP 7.4 or later
- cURL PHP extension
- JSON PHP extension

## Installation

Grab the library using [Composer](https://getcomposer.org/):

```bash
composer require koba9813/irodori-translate-engine
```

## Usage

### Basic Example

Here's a straightforward way to run a translation. Always wrap it in try-catch for solid error handling.

```php
<?php

require 'vendor/autoload.php';

use Irodori\Honyaku\Translator;

// Best practice: pull your API key from an environment variable.
$apiKey = getenv('SAKURA_API_KEY');
if (!$apiKey) {
    die('API key not set!');
}

try {
    // 1. Create a new Translator instance
    $translator = new Translator($apiKey);

    // 2. Set up your translation options
    $options = [
        'text' => 'こんにちは、世界！',
        'target' => 'english', // Language to translate to
        'source' => 'japanese'  // Source language ('auto' works too)
    ];

    // 3. Run the translation
    $result = $translator->translate($options);

    // 4. Output the result
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (InvalidArgumentException $e) {
    // Bad input, like empty text
    http_response_code(400);
    echo json_encode(['error' => 'Bad request: ' . $e->getMessage()]);
} catch (RuntimeException $e) {
    // API or response issues
    http_response_code(503);
    echo json_encode(['error' => 'Service down: ' . $e->getMessage()]);
} catch (Throwable $e) {
    // Catch-all for surprises
    http_response_code(500);
    echo json_encode(['error' => 'Server hiccup: ' . $e->getMessage()]);
}
```

### Available Options

Pass these keys in the `$options` array to `translate()`:

| Key             | Type     | Default     | Description                                                                 |
|-----------------|----------|-------------|-----------------------------------------------------------------------------|
| `text`          | string   | **Required**| The text to translate (max 1500 chars).                                     |
| `target`        | string   | `'english'` | Target language (e.g., `'japanese'`, `'english'`, `'french'`).              |
| `source`        | string   | `'auto'`    | Source language. Use `'auto'` for automatic detection.                      |
| `is_literal`    | boolean  | `false`     | Set to `true` for word-for-word literal translation (default is natural).   |
| `style`         | string   | `'standard'`| Style tweaks per language (e.g., Japanese: `'casual'`, `'polite'`; English: `'american'`, `'british'`). |
| `custom_prompt` | string   | `''`        | Your own instructions when `style` is `'custom'`—overrides everything else. |

## License

MIT License. Check the [LICENSE](LICENSE) file for the full details.

-------------------------------------------------------------------------------------------

<a id="japanese"></a>
# irodori Translate Engine

さくらインターネットの「さくらのAI Engine」で提供されているAIのAPIを翻訳に利用するための、シンプルなPHPラッパーです。「Irodori翻訳」で実際に使われている翻訳処理のコア部分を切り出してライブラリ化しています。

## 特徴

- シンプルで直感的に使える、クラスベースのAPI設計
- 言語指定やスタイル設定など、APIの翻訳オプションを幅広くサポート
- 「自然な翻訳」と「直訳」モードの簡単な切り替え。
- 翻訳スタイルを細かく調整できるカスタムプロンプトに対応
## 要件

- PHP 7.4 以上
- cURL PHP拡張機能
- JSON PHP拡張機能

## インストール

ライブラリを [Composer](https://getcomposer.org/) 経由でインストールします。

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
| `text`          | string  | **必須**    | 翻訳するテキスト（最大1500文字）。                                                                                                       |
| `target`        | string  | `'english'` | 翻訳先の言語ID（例: `'japanese'`, `'english'`, `'french'`）。                                                                            |
| `source`        | string  | `'auto'`    | 翻訳元の言語ID。`'auto'` を指定すると自動で言語を検出します。                                                                           |
| `is_literal`    | boolean | `false`     | `true` の場合、厳密な直訳を実行します。デフォルトはより自然な翻訳です。                                                                  |
| `style`         | string  | `'standard'`| 翻訳のスタイル。対象言語によって異なります（例: 日本語の `'casual'`, `'polite'`、英語の `'american'`, `'british'` など）。         |
| `custom_prompt` | string  | `''`        | `style`が`'custom'`の時に使用する、翻訳へのカスタム指示。他のスタイル設定を上書きします。                                             |

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。詳細は [LICENSE](LICENSE) ファイルをご覧ください。
