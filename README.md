# スーパーロボット大戦Wiki 概要テンプレートエディタ

スーパーロボット大戦Wikiの概要テンプレートの編集を支援するMediaWiki拡張機能です。

この拡張機能は、特別ページ「特別:概要テンプレートエディタ」を追加します。この特別ページより、概要テンプレートを作成するためのウィキマークアップをフォームを使用して生成することができます。

## 動作環境

* PHP 5.5以降
* MediaWiki 1.25以降

## インストール方法

MediaWikiの拡張機能ディレクトリにこの拡張機能のディレクトリを作成し、ファイルを展開します。GitHubのリポジトリをcloneする場合は、以下を実行します。

```bash
cd /path/to/mediawiki/extensions
git clone https://github.com/cre-ne-jp/SrwWikiSummaryTemplateEditor.git
```

ファイルの展開後、LocalSettings.phpの末尾に以下を追加します。

```
wfLoadExtension('SrwWikiSummaryTemplateEditor');
```

## ライセンス

[MIT License](LICENSE)

## 制作

&copy; ocha（[@ochaochaocha3](https://github.com/ochaochaocha3)）
