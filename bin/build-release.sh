#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SLUG="reno-plus-divi5-breadcrumbs"
BUILD_ROOT="$ROOT/.release"
BUILD_DIR="$BUILD_ROOT/$SLUG"
DIST_DIR="$ROOT/dist"

cd "$ROOT"

if [[ -d node_modules ]]; then
  npm run build
else
  printf '%s\n' 'node_modules is absent; using the committed prebuilt Divi 5 assets.'
fi

rm -rf "$BUILD_ROOT"
mkdir -p "$BUILD_DIR" "$DIST_DIR"

cp -R \
  reno-plus-divi5-breadcrumbs.php \
  includes \
  modules \
  modules-json \
  scripts \
  styles \
  README.md \
  readme.txt \
  CHANGELOG.md \
  LICENSE \
  "$BUILD_DIR/"

rm -f "$DIST_DIR/$SLUG.zip"
(
  cd "$BUILD_ROOT"
  zip -qr "$DIST_DIR/$SLUG.zip" "$SLUG"
)

printf 'Created %s\n' "$DIST_DIR/$SLUG.zip"
