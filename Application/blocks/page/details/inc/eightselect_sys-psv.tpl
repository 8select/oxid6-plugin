[{if !$oViewConf}]
    [{assign var="oViewConf" value=$oView->getConfig()}]
[{/if}]

[{if $oViewConf->isEightSelectActive() && $oViewConf->showEightSelectWidget('sys-psv')}]
    [{if !$oDetailsProduct}]
        [{assign var="oDetailsProduct" value=$oView->getProduct()}]
    [{/if}]
    <div class="-eightselect-widget-container" style="display: none;">
        <div data-sku="[{$oDetailsProduct->getEightSelectVirtualSku()}]" data-8select-widget-id="sys-psv" data-load-distance-factor="0"></div>
    </div>
    <script type="text/javascript">
        if (window._8select !== undefined) {
            window._8select.initCSE();
        }
    </script>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
