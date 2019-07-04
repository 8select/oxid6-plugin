<?php

namespace ASign\EightSelect\Application\Controller;

use ASign\EightSelect\Core\Attribute;
use ASign\EightSelect\Core\Export;
use ASign\EightSelect\Application\Model\Log;
use OxidEsales\Eshop\Core\Controller\BaseController;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Eshop\Core\UtilsServer;

/**
 * Product data API controller
 */
class EightSelectAPI extends BaseController
{
    /** @var array */
    protected $fields;
    /** @var array */
    protected $requiredFields = [
        'oxarticles.OXID',
        'oxarticles.OXPARENTID',
        'oxarticles.OXTITLE',
        'oxarticles.OXPRICE',
        'oxarticles.OXTPRICE',
        'oxseo.URL',
        'product.PICTURES',
        'product.SKU',
        'product.BUYABLE',
    ];

    /**
     * Check credentials submitted in header
     */
    public function init()
    {
        parent::init();
        header("Content-Type: application/json; charset=utf-8");

        $error = ['error' => 'AUTH_ERROR'];

        // First: Check if we even got those IDs configured
        $apiId = $this->getConfig()->getConfigParam('sEightSelectApiId');
        $feedId = $this->getConfig()->getConfigParam('sEightSelectFeedId');
        if (!$apiId || !$feedId) {
            header("HTTP/1.1 500 Internal Server Error");
            $error['message'] = 'credentials not configured';
            die(json_encode($error));
        }

        // Second: Check if the credentials were even sent
        $givenApiId = Registry::get(UtilsServer::class)->getServerVar('HTTP_8SELECT_COM_TID');
        $givenFeedId = Registry::get(UtilsServer::class)->getServerVar('HTTP_8SELECT_COM_FID');
        if (!$givenApiId || !$givenFeedId) {
            header("HTTP/1.0 404 Not Found");
            die(); // No output
        }

        // Third: Check if the given credentials match with ours
        if ($givenFeedId !== $feedId || $givenApiId !== $apiId) {
            header("HTTP/1.1 403 Forbidden");
            $error['message'] = 'credential mismatch';
            die(json_encode($error));
        }
    }

    /**
     * Loads all relevant data for response and outputs it
     *
     * @return null|void
     */
    public function render()
    {
        $response = [
            'limit'  => $this->getLimit(),
            'offset' => $this->getOffset(),
            'total'  => $this->getTotalArticlesSum(),
            'data'   => $this->getData(),
        ];

        die(json_encode($response));
    }

    /**
     * Endpoint listing all attributes/fields available for export
     */
    public function renderAttributes()
    {
        $data = Registry::get(Attribute::class)->getAllFields();
        $response = [
            'limit'  => count($data),
            'offset' => 0,
            'total'  => count($data),
            'data'   => $data,
        ];

        die(json_encode($response));
    }

    /**
     * Endpoint listing all attributes/fields relevant for building variants
     */
    public function renderVariantDimensions()
    {
        $data = Registry::get(Attribute::class)->getVarNames();
        $response = [
            'limit'  => count($data),
            'offset' => 0,
            'total'  => count($data),
            'data'   => $data,
        ];

        die(json_encode($response));
    }

    /**
     * Loads article data
     *
     * @return array
     */
    protected function getData()
    {
        $data = [];
        $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');
        $limit = $this->getLimit();
        $offset = $this->getOffset();

        $fullExport = !$this->isDeltaExport();

        $where = '';
        if (!$fullExport) {
            $log = oxNew(Log::class);
            $dateTime = $log->getLastSuccessExportDate($fullExport);
            if (!$dateTime) {
                $dateTime = DatabaseProvider::getDb()->getOne('SELECT NOW()');
            }
            $dateTime = DatabaseProvider::getDb()->quote($dateTime);
            $where = "WHERE OXTIMESTAMP > $dateTime";
        }

        $requiredArticleFields = $this->getRequiredArticleFields();
        $query = "SELECT " . implode(', ', $requiredArticleFields) . " FROM $view $where LIMIT $offset, $limit";

        $articleData = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getAll($query);
        foreach ($articleData as $article) {
            $data[] = $this->buildFullArticleData($article);
        }

        $log = oxNew(Log::class);
        $log->setLastSuccessExportDate($fullExport);

        return $data;
    }

    /**
     * Collects required article fields from fields
     *
     * @return array
     */
    protected function getRequiredArticleFields()
    {
        $fields = $this->getFields();
        $requiredArticleFields = [];
        foreach ($fields as $fieldData) {
            list($table, $field) = explode('.', $fieldData['name']);

            if ($table === 'oxarticles') {
                $requiredArticleFields[] = $field;
            } elseif ($table === 'oxvarname') {
                $requiredArticleFields[] = 'OXVARNAME';
                $requiredArticleFields[] = 'OXVARSELECT';
            } elseif ($table === 'oxvendor') {
                $requiredArticleFields[] = 'OXVENDORID';
            } elseif ($table === 'oxmanufacturers') {
                $requiredArticleFields[] = 'OXMANUFACTURERID';
            } elseif ($table === 'product' && $field === 'PICTURES') {
                for ($i = 1; $i <= 12; $i++) {
                    $requiredArticleFields[] = 'OXPIC' . $i;
                }
            } elseif ($table === 'product' && $field === 'SKU') {
                $requiredArticleFields[] = $this->getConfig()->getConfigParam('sArticleSkuField');
            }
        }

        return array_unique($requiredArticleFields);
    }

    /**
     * Merges article data with parent data and starts exporter
     *
     * @param array $articleData Article data
     * @return array
     */
    protected function buildFullArticleData($articleData)
    {
        // Merge parent data into variant data
        if ($articleData['OXPARENTID']) {
            $requiredArticleFields = $this->getRequiredArticleFields();
            $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');
            $query = "SELECT " . implode(', ', $requiredArticleFields) . " FROM $view WHERE OXID = ?";
            $parentData = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC)->getRow($query, [$articleData['OXPARENTID']]);

            foreach ($articleData as $field => $value) {
                if (!$value && $parentData[$field]) {
                    $articleData[$field] = $parentData[$field];
                }
            }
        }

        $export = oxNew(Export::class);

        return $export->getExportData($this->getFields(), $articleData, $this->requiredFields);
    }

    /**
     * Returns article limit for pagination
     *
     * @return int
     */
    protected function getLimit()
    {
        $limit = (int) Registry::get(Request::class)->getRequestEscapedParameter('limit');
        if (!$limit) {
            $limit = 100;
        }

        return $limit;
    }

    /**
     * Returns offset for pagination
     *
     * @return int
     */
    protected function getOffset()
    {
        $offset = (int) Registry::get(Request::class)->getRequestEscapedParameter('offset');
        if (!$offset) {
            $offset = 0;
        }

        return $offset;
    }

    /**
     * Checks if the current call is for a full export or a delta export
     *
     * @return bool
     */
    protected function isDeltaExport()
    {
        $isDelta = false;

        $parameter = Registry::get(Request::class)->getRequestEscapedParameter('delta');
        if ($parameter) {
            $isDelta = true;

            if ($parameter === 'false') {
                $isDelta = false;
            }
        }

        return $isDelta;
    }

    /**
     * Returns number of all articles
     *
     * @return int
     */
    protected function getTotalArticlesSum()
    {
        $view = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');

        return (int) DatabaseProvider::getDb()->getOne("SELECT COUNT(1) FROM $view");
    }

    /**
     * Returns requested/all fields
     *
     * @return array
     */
    protected function getFields()
    {
        if (is_null($this->fields)) {
            $data = Registry::get(Attribute::class)->getAllFields();
            $fields = $data;
            if ($requestedFields = Registry::get(Request::class)->getRequestEscapedParameter('fields')) {
                $fields = [];
                foreach ($data as $fieldData) {
                    if (in_array($fieldData['name'], $requestedFields, true)
                        || in_array($fieldData['name'], $this->requiredFields, true)
                    ) {
                        $fields[] = $fieldData;
                    }
                }
            }

            $this->fields = $fields;
        }

        return $this->fields;
    }
}
