PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Oxid-6_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "=========================="
echo "BUILDING"
echo "VERSION: ${VERSION}"
echo "PROFILE: ${PROFILE}"
echo "=========================="

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../.." "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
rm -rf vendor
rm -rf bin/lib
rm -f bin/release.sh
rm -rf dist
rm -rf .git*

sed -i '' "s@__VERSION__@${VERSION}@g" metadata.php
sed -i '' "s@__VERSION__@${VERSION}@g" composer.json

TEMPLATE_PATH="Application/blocks/base_style.tpl"
MODULE_CONFIG_PATH="Extensions/Application/Controller/Admin/ModuleConfiguration.php"

if [ ${PROFILE} == 'production' ]
then
  sed -i '' "s@__SUBDOMAIN__@wgt@g" ${TEMPLATE_PATH}
  sed -i '' "s@__SUBDOMAIN__@sc@g" ${MODULE_CONFIG_PATH}
else
  sed -i '' "s@__SUBDOMAIN__@wgt-prod.${PROFILE}@g" ${TEMPLATE_PATH}
  sed -i '' "s@__SUBDOMAIN__@sc-prod.${PROFILE}@g" ${MODULE_CONFIG_PATH}
fi
