S3_ACCESS_KEY=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name product-feed-service-prod --query 'Stacks[0].Outputs[?OutputKey==`PluginUserAccessKeyId`].OutputValue' --output text)
S3_ACCESS_KEY_SECRET=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name product-feed-service-prod --query 'Stacks[0].Outputs[?OutputKey==`PluginUserAccessKeySecret`].OutputValue' --output text)

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

sed -i '' "s@__VERSION__@${VERSION}@g" modules/asign/8select/metadata.php

TPL_PATH="modules/asign/8select/application/views/blocks/base_style.tpl"

if [ ${PROFILE} == 'production' ]
then
  sed -i '' "s@__SUBDOMAIN__@wgt@g" ${TPL_PATH}
else
  sed -i '' "s@__SUBDOMAIN__@wgt-prod.${PROFILE}@g" ${TPL_PATH}
fi
