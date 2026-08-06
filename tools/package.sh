#!/usr/bin/env bash
set -euo pipefail

version="$(php -r "require 'setup.php'; echo PLUGIN_ONCALLFORMS_VERSION;")"
build_dir="build"
stage_dir="${build_dir}/oncallforms"

rm -rf "${build_dir}"
mkdir -p "${stage_dir}"
tar \
  --exclude='./.git' \
  --exclude='./.codebase-memory' \
  --exclude='./.composer-temp' \
  --exclude='./build' \
  --exclude='./vendor' \
  --exclude='./composer.lock' \
  -cf - . | tar -xf - -C "${stage_dir}"
rm -rf "${stage_dir}/tests" "${stage_dir}/docs" "${stage_dir}/tools" "${stage_dir}/.github"
rm -f "${stage_dir}/phpunit.xml.dist" "${stage_dir}/phpstan.neon" "${stage_dir}/phpcs.xml.dist"
rm -f "${stage_dir}/.editorconfig" "${stage_dir}/.gitattributes" "${stage_dir}/.gitignore"
(cd "${build_dir}" && zip -qr "oncallforms-${version}.zip" oncallforms)
