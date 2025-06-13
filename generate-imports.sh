#!/bin/bash

# エラーが発生した場合にスクリプトを停止
set -e

# スクリプトのディレクトリを取得
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# 出力ファイルのパス
OUTPUT_FILE="$SCRIPT_DIR/src/assets/js/script.js"

# 出力ファイルを初期化
echo "// 自動生成されたインポートファイル" > "$OUTPUT_FILE"
echo "// このファイルは直接編集しないでください" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

# jsディレクトリのフルパス
JS_DIR="$SCRIPT_DIR/src/assets/js"

# ディレクトリ内のJSファイルをインポート
find "$JS_DIR" -name "*.js" | sort | while read -r file; do
    # 相対パスを取得（src/assets/js/を除く）
    relative_path="${file#$JS_DIR/}"
    # インポート文を追加
    echo "import './$relative_path';" >> "$OUTPUT_FILE"
done

echo "インポートファイルの生成が完了しました: $OUTPUT_FILE" 