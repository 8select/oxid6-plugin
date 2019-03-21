<?php

namespace ASign\EightSelect\Model;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class EightSelectAws
 * @package ASign\EightSelect\Model
 */
class Aws extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    const CREDENTIAL_PROD_BUCKET_URL = '__SUBDOMAIN__.8select.io';
    const CREDENTIAL_PROD_KEY = '__S3_PLUGIN_USER_ACCESS_KEY__';
    const CREDENTIAL_PROD_SEC = '__S3_PLUGIN_USER_ACCESS_KEY_SECRET__';

    /**
     * @var string
     */
    protected $_sExportRemotePath = '#FEEDID#/#FEEDTYPE#/#YEAR#/#MONTH#/#DAY#/';

    /**
     * @param string $sourceFile
     * @param string $feedId
     * @param bool   $full
     * @throws \Exception
     */
    public function upload($sourceFile, $feedId, $full)
    {
        $log = oxNew(Log::class);
        $action = 'Upload ' . ($full ? 'Full' : 'Update');

        try {
            $awsS3Client = new S3Client([
                'region'      => 'eu-central-1',
                'version'     => '2006-03-01',
                'credentials' => [
                    'key'    => $this->_getCredentialKey(),
                    'secret' => $this->_getCredentialSecret(),
                ],
            ]);

            $awsS3Client->putObject([
                'ACL'        => 'bucket-owner-full-control',
                'Bucket'     => $this->_getBucketUrl(),
                'Key'        => $this->_getRemotePath($feedId, $full) . basename($sourceFile),
                'SourceFile' => $sourceFile,
            ]);

            Registry::get(Export::class)->clearExportLocalFolder($full);

            $log->addLog($action, 'Upload successfully');
        } catch (S3Exception $exception) {
            $log->addLog($action, "AWS S3Exception - Upload error\n" . $exception->getMessage());
            throw new \UnexpectedValueException('Upload fails');
        } catch (\Exception $exception) {
            $log->addLog($action, "AWS Exception - Upload error\n" . $exception->getMessage());
            throw new \UnexpectedValueException('Upload fails');
        }
    }

    /**
     * @return string
     */
    protected function _getBucketUrl()
    {
        return self::CREDENTIAL_PROD_BUCKET_URL;
    }

    /**
     * @return string
     */
    protected function _getCredentialKey()
    {
        return self::CREDENTIAL_PROD_KEY;
    }

    /**
     * @return string
     */
    protected function _getCredentialSecret()
    {
        return self::CREDENTIAL_PROD_SEC;
    }

    /**
     * @param string $feedId
     * @param bool   $full
     * @return string
     */
    protected function _getRemotePath($feedId, $full)
    {
        $params = [
            '#FEEDID#'   => $feedId,
            '#FEEDTYPE#' => $full ? 'product_feed' : 'property_feed',
            '#YEAR#'     => date('Y'),
            '#MONTH#'    => date('m'),
            '#DAY#'      => date('d'),
        ];

        return str_replace(array_keys($params), $params, $this->_sExportRemotePath);
    }
}
