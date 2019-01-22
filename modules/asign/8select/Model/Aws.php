<?php

namespace ASign\EightSelect\Model;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

/**
 * Class EightSelectAws
 * @package ASign\EightSelect\Model
 */
class Aws extends \OxidEsales\Eshop\Core\Model\BaseModel
{
    CONST CREDENTIAL_PROD_BUCKET_URL = 'productfeed.8select.io';
    CONST CREDENTIAL_PROD_KEY = 'AKIAJT2DMZOXIT6PQB7A';
    CONST CREDENTIAL_PROD_SEC = 'Hk++KYdwWQLlRTuy0P05zsKdn+JDqrc849CSINHy';

    /**
     * @var string
     */
    static private $_sExportRemotePath = '#FEEDID#/#FEEDTYPE#/#YEAR#/#MONTH#/#DAY#/';

    /**
     * @param string $sSourceFile
     * @param string $sFeedId
     * @param bool $blFull
     */
    public static function upload($sSourceFile, $sFeedId, $blFull)
    {
        $oEightSelectLog = oxNew(\ASign\EightSelect\Model\Log::class);
        $sAction = 'Upload ' . ($blFull ? 'Full' : 'Update');

        try {
            $s3Client = new S3Client([
                'region'      => 'eu-central-1',
                'version'     => '2006-03-01',
                'credentials' => [
                    'key'    => self::_getCredentialKey(),
                    'secret' => self::_getCredentialSecret(),
                ],
            ]);

            $s3Client->putObject([
                'ACL'        => 'bucket-owner-full-control',
                'Bucket'     => self::_getBucketUrl(),
                'Key'        => self::_getRemotePath($sFeedId, $blFull) . basename($sSourceFile),
                'SourceFile' => $sSourceFile,
            ]);

            \ASign\EightSelect\Model\Export::clearExportLocalFolder($blFull);

            $oEightSelectLog->addLog($sAction, 'Upload successfully');
        } catch (S3Exception $oEx) {
            $oEightSelectLog->addLog($sAction, "AWS S3Exception - Upload error\n" . $oEx->getMessage());
            throw new \UnexpectedValueException('Upload fails');
        } catch (\Exception $oEx) {
            $oEightSelectLog->addLog($sAction, "AWS Exception - Upload error\n" . $oEx->getMessage());
            throw new \UnexpectedValueException('Upload fails');
        }
    }

    /**
     * @return string
     */
    private static function _getBucketUrl()
    {
        return self::CREDENTIAL_PROD_BUCKET_URL;
    }

    /**
     * @return string
     */
    private static function _getCredentialKey()
    {
        return self::CREDENTIAL_PROD_KEY;
    }

    /**
     * @return string
     */
    private static function _getCredentialSecret()
    {
        return self::CREDENTIAL_PROD_SEC;
    }

    /**
     * @param $sFeedId
     * @param $blFull
     * @return mixed
     */
    private static function _getRemotePath($sFeedId, $blFull)
    {
        $aParams = [
            '#FEEDID#'   => $sFeedId,
            '#FEEDTYPE#' => $blFull ? 'product_feed' : 'property_feed',
            '#YEAR#'     => date('Y'),
            '#MONTH#'    => date('m'),
            '#DAY#'      => date('d'),
        ];

        $sPath = str_replace(array_keys($aParams), $aParams, self::$_sExportRemotePath);

        return $sPath;
    }
}
