S3_ACCESS_KEY=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name product-feed-service-prod --query 'Stacks[0].Outputs[?OutputKey==`PluginUserAccessKeyId`].OutputValue' --output text)
S3_ACCESS_KEY_SECRET=$(aws --profile ${PROFILE} --region eu-central-1 cloudformation describe-stacks --stack-name product-feed-service-prod --query 'Stacks[0].Outputs[?OutputKey==`PluginUserAccessKeySecret`].OutputValue' --output text)

PLUGIN_NAME="CseEightselectBasic"

DIST_DIR="dist"
ZIP_NAME="${PLUGIN_NAME}_Oxid-EE-5_${VERSION}.zip"
DIST_PATH="${CURRENT_DIR}/../../${DIST_DIR}/${ZIP_NAME}"
BUILD_DIR=`mktemp -d`
PLUGIN_DIR="${BUILD_DIR}/${PLUGIN_NAME}"

echo "=========================="
echo "BUILDING"
echo "VERSION: ${VERSION}"
echo "PROFILE: ${PROFILE}"
echo "S3_ACCESS_KEY: ${S3_ACCESS_KEY}"
echo "S3_ACCESS_KEY_SECRET: ${S3_ACCESS_KEY_SECRET}"
echo "=========================="

echo "Build at ${BUILD_DIR}"
cp -r "${CURRENT_DIR}/../.." "${BUILD_DIR}/${PLUGIN_NAME}"
cd ${PLUGIN_DIR}
rm -rf vendor
rm -rf bin/lib
rm -f bin/release.sh
rm -rf dist

sed -i '' "s@__VERSION__@${VERSION}@g" modules/asign/8select/metadata.php

TPL_PATH="modules/asign/8select/application/views/blocks/base_style.tpl"
UPLOADER_PATH="modules/asign/8select/models/eightselect_aws.php"

if [ ${PROFILE} == 'production' ]
then
  sed -i '' "s@__SUBDOMAIN__@productfeed@g" ${UPLOADER_PATH}
  sed -i '' "s@__SUBDOMAIN__@wgt@g" ${TPL_PATH}
else
  sed -i '' "s@__SUBDOMAIN__@productfeed-prod.${PROFILE}@g" ${UPLOADER_PATH}
  sed -i '' "s@__SUBDOMAIN__@wgt-prod.${PROFILE}@g" ${TPL_PATH}
fi

sed -i '' "s@__S3_PLUGIN_USER_ACCESS_KEY__@${S3_ACCESS_KEY}@g" ${UPLOADER_PATH}
sed -i '' "s@__S3_PLUGIN_USER_ACCESS_KEY_SECRET__@${S3_ACCESS_KEY_SECRET}@g" ${UPLOADER_PATH}
