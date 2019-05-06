<?php

namespace ASign\EightSelect\Extensions\Application\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\TableViewNameGenerator;

/**
 */
class ModuleConfiguration extends ModuleConfiguration_parent
{
    /**
     * getEightSelectFields
     * -----------------------------------------------------------------------------------------------------------------
     * Returns all possible fields for SKU or color
     *
     * @return array
     */
    public function getEightSelectFields()
    {
        $aSelectAttributes = [];
        $oLang = Registry::getLang();

        // Default static Oxid fields
        $aArticleFields = [
            ['oxarticles;OXARTNUM', $oLang->translateString('ARTICLE_MAIN_ARTNUM')],
            ['oxarticles;OXTITLE', $oLang->translateString('ARTICLE_MAIN_TITLE')],
            ['oxarticles;OXSHORTDESC', $oLang->translateString('GENERAL_ARTICLE_OXSHORTDESC')],
            ['oxartextends;OXLONGDESC', $oLang->translateString('GENERAL_ARTICLE_OXLONGDESC')],
            ['oxarticles;OXEAN', $oLang->translateString('ARTICLE_MAIN_EAN')],
            ['oxarticles;OXWIDTH', $oLang->translateString('GENERAL_ARTICLE_OXWIDTH')],
            ['oxarticles;OXHEIGHT', $oLang->translateString('GENERAL_ARTICLE_OXHEIGHT')],
            ['oxarticles;OXLENGTH', $oLang->translateString('GENERAL_ARTICLE_OXLENGTH')],
        ];

        $sOptGroupAttribute = $oLang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ARTICLE');
        foreach ($aArticleFields as $aArticleField) {
            $aSelectAttributes[$sOptGroupAttribute][$aArticleField[0]] = $aArticleField[1];
        }

        // Dynamic attributes
        $sTableName = Registry::get(TableViewNameGenerator::class)->getViewName('oxattribute');
        $aAttributes = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getAll("SELECT CONCAT('oxattribute;', OXID), OXTITLE FROM {$sTableName}");
        $sOptGroupAttribute = $oLang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_ATTRIBUTE');
        foreach ($aAttributes as $aAttribute) {
            $aSelectAttributes[$sOptGroupAttribute][$aAttribute[0]] = $aAttribute[1];
        }

        // Dynamic variant selections
        $sTableName = Registry::get(TableViewNameGenerator::class)->getViewName('oxarticles');
        $aVarSelect = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_NUM)->getCol("SELECT DISTINCT OXVARNAME FROM {$sTableName} WHERE OXVARNAME != ''");
        $sOptGroupVarSelect = $oLang->translateString('EIGHTSELECT_ADMIN_ATTRIBUTE_OPTGROUP_VARSELECT');
        foreach ($aVarSelect as $sVarSelect) {
            $aDiffVarSelect = explode(' | ', $sVarSelect);
            foreach ($aDiffVarSelect as $sDiffVarSelect) {
                $sDiffVarSelect = trim($sDiffVarSelect);
                $aSelectAttributes[$sOptGroupVarSelect]['oxvarselect;' . $sDiffVarSelect] = $sDiffVarSelect;
            }
        }

        return $aSelectAttributes;
    }
}
