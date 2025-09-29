# INVESTIGATE フェーズ

# ユーザの入力
#$ARGUMENTS 

## 目的
背景・要件・制約の把握を行い、実装の方向性を決定する。

## 注意事項
- 関連するコードは全て読むこと。
- 全ての処理においてultrathinkでしっかりと考えて作業を行うこと。
- ファイルへの記述において絵文字を使用してはいけません。
- Do not make any changes, until you have 95% confidence that you know what to build ask me follow up questions until you have that confidence

## タスクに含まれるべきTODO
2. 調査対象・スコープを明確化
3. 現在のブランチ状況を確認し、`feature/<topic>` ブランチを作成
4. 関連ファイル・ログ・ドキュメントを収集し、体系的に分析
5. 技術的制約・可能性を調査
6. 既存システムとの整合性を確認
7. 問題の根本原因・解決方針を特定
8. 調査結果を文書化し、`docs/investigate/investigate_{TIMESTAMP}.md`に保存
9. 次フェーズ（Plan）への推奨事項を提示
10. 調査完了とファイル保存に関して`afplay /System/Library/Sounds/Sosumi.aiff`を実行しユーザに通知
11. 作成したブランチ名、調査結果ファイル名をコンソール出力

## ブランチ作成規則
- ブランチ命名: `feature/<topic>` または `fix/<issue>`
- 必ず `main` ブランチから作成
- ブランチ作成後はそのブランチで全作業を継続

## 出力ファイル
- `docs/investigate/investigate_{TIMESTAMP}.md`

## 最終出力形式
- 必ず以下の二つの形式で出力を行ってください

### 調査完了（実装推奨）の場合
status: COMPLETED
next: PLAN
details: "調査完了。feature/<topic>ブランチ作成済み。docs/investigate/investigate_{TIMESTAMP}.mdに詳細を記述。実装方針策定を推奨。"

### 調査完了（実装不要）の場合
status: COMPLETED
next: NONE
details: "調査完了。docs/investigate/investigate_{TIMESTAMP}.mdに詳細を記述既存機能で対応可能。実装・変更は不要。"
