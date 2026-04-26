#!/usr/bin/env bash
# seed-kb.sh — Seed WebberZone Knowledge Base with demo content.
#
# Usage: bash dev-tools/seed-kb.sh /path/to/wordpress
#
set -euo pipefail

WP_PATH="${1:-}"

if [[ -z "$WP_PATH" ]]; then
	echo "Error: provide the WordPress path as the first argument."
	echo "Usage: bash seed-kb.sh /path/to/wordpress"
	exit 1
fi

if ! command -v wp &>/dev/null; then
	echo "Error: wp (WP-CLI) not found on PATH."
	exit 1
fi

WP="wp --path=${WP_PATH}"

echo "==> WordPress path: ${WP_PATH}"
echo ""

# Install and activate the plugin if not already active.
if $WP plugin is-active knowledgebase 2>/dev/null; then
	echo "[ok] knowledgebase plugin already active."
else
	echo "[..] Installing and activating knowledgebase plugin..."
	$WP plugin install knowledgebase --activate
fi

# Run the PHP seeder.
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
echo ""
echo "[..] Running content seeder..."
$WP eval-file "${SCRIPT_DIR}/seed-kb-content.php"

echo ""
echo "==> Done!"
echo ""
echo "Article count : $($WP post list --post_type=wz_knowledgebase --post_status=publish --format=count)"
echo "Category count: $($WP term list wzkb_category --format=count)"
echo "Tag count     : $($WP term list wzkb_tag --format=count)"
