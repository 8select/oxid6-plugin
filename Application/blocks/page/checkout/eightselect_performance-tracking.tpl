[{$smarty.block.parent}]

[{if !$oViewConf}]
    [{assign var="oViewConf" value=$oView->getConfig()}]
[{/if}]

[{if $oViewConf->isEightSelectActive()}]
    [{if !$order}]
        [{assign var="order" value=$oView->getOrder()}]
    [{/if}]

    <script type="text/javascript">
        window.eightlytics(
            'purchase',
            {
                customerid: '[{$oxcmp_user->oxuser__oxcustnr->value}]',
                orderid: '[{$order->oxorder__oxordernr->value}]',
                products: [
                    [{foreach from=$order->getOrderArticles(true) item=orderitem name=eightitem}]
                    {
                        sku: '[{$orderitem->oxorderarticles__oxartnum->value}]',
                        amount: [{$orderitem->oxorderarticles__oxamount->value}],
                        price: [{$orderitem->oxorderarticles__oxprice->value|number_format:2:"":""}]
                    }[{if !$smarty.foreach.eightitem.last}],[{/if}]
                    [{/foreach}]
                ]
            }
        );
    </script>
[{/if}]
