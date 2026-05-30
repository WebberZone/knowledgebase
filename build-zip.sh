#!/bin/bash
# Build script for creating distribution zip
# Only includes production files and runtime dependencies

set -e

PLUGIN_SLUG="knowledgebase"
BUILD_DIR="build"
TEMP_DIR="$BUILD_DIR/$PLUGIN_SLUG"

echo "Creating distribution zip for $PLUGIN_SLUG..."

# Clean build directory
rm -rf "$BUILD_DIR"
mkdir -p "$TEMP_DIR"

# Copy plugin files (excluding dev/build artifacts and all of vendor)
echo "Copying plugin files..."
rsync -av --exclude-from=- . "$TEMP_DIR/" <<EOF
.*
.git/
.github/
node_modules/
phpcompat-tools/
phpunit/
/build/
vendor/
dev-helpers/
dev-tools/
wporg-assets/
test-tools/
docs/
includes/frontend/blocks/src/
includes/pro/blocks/src/
build-assets.js
*.dist
*.yml
*.neon
composer.json
composer.lock
package.json
package-lock.json
phpstan-bootstrap.php
build-zip.sh
CODE_OF_CONDUCT.md
CONTRIBUTING.md
ISSUE_TEMPLATE.md
PULL_REQUEST_TEMPLATE.md
CLAUDE.md
AGENTS.md
EOF

# Copy required vendor dependencies (everything in vendor/ is excluded above,
# so production runtime deps must be copied back in explicitly). Dev-only files
# such as .github workflow folders are stripped from the copies.
echo "Copying vendor dependencies..."
mkdir -p "$TEMP_DIR/vendor"

# Freemius SDK (manually bundled).
if [ -d "vendor/freemius" ]; then
    rsync -a --exclude='.github' --exclude='.git*' vendor/freemius "$TEMP_DIR/vendor/"
else
    echo "Warning: vendor/freemius directory not found. Freemius SDK will be missing."
fi

# Parsedown (Markdown parser used by the GitHub content importer; loaded via a
# direct require_once, not the Composer autoloader). Only needed in pro builds.
if [ -d "includes/pro" ]; then
    if [ -d "vendor/erusev/parsedown" ]; then
        mkdir -p "$TEMP_DIR/vendor/erusev"
        rsync -a --exclude='.github' --exclude='.git*' vendor/erusev/parsedown "$TEMP_DIR/vendor/erusev/"
    else
        echo "Warning: vendor/erusev/parsedown directory not found. Markdown import will fail."
    fi
fi

# Create zip
echo "Creating zip file..."
cd "$BUILD_DIR"
zip -r "$PLUGIN_SLUG.zip" "$PLUGIN_SLUG/" -q

echo "✓ Distribution zip created: $BUILD_DIR/$PLUGIN_SLUG.zip"
cd ..

# Show zip contents summary
echo ""
echo "Zip contents summary:"
unzip -l "$BUILD_DIR/$PLUGIN_SLUG.zip" | tail -1
