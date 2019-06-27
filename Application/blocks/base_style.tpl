[{if !$oViewConf}]
    [{assign var="oViewConf" value=$oView->getConfig()}]
[{/if}]

[{if $oViewConf->isEightSelectActive()}]
    <script type="text/javascript">
        (function (d, s, w) {
            var apiId = '[{ $oViewConf->getEightSelectApiId() }]';

            window.eightlytics || function (w) {
                w.eightlytics = function () {
                    window.eightlytics.queue = window.eightlytics.queue || [];
                    window.eightlytics.queue.push(arguments);
                };
            }(w);
            var script = d.createElement(s);
            script.src = 'https://__SUBDOMAIN__.8select.io/' + apiId + '/loader.js';
            var entry = d.getElementsByTagName(s)[0];
            entry.parentNode.insertBefore(script, entry);
        })(document, 'script', window);
    </script>

    [{if $oViewConf->showEightSelectWidget('sys-psv')}]
        <script type="text/javascript">
            window._eightselect_config = window._eightselect_config || [];
            window._eightselect_config['sys'] = {
                callback: function (error, sku, widgetUuid) {
                    if (error) {
                        return;
                    }
                    document.querySelector('[data-8select-widget-id=sys-psv]').style.display = 'block';
                }
            };
            window._stoken = "[{$oViewConf->getSessionChallengeToken()}]";
            window._eightselect_shop_plugin = window._eightselect_shop_plugin || {};
            window._eightselect_shop_plugin.addToCart = function(sku, quantity, Promise) {
                var jqueryFail = function(reject) {
                    return function(jqXHR, textStatus, errorThrown) {
                        return reject(errorThrown);
                    }
                };

                return new Promise(function(resolve, reject) {
                    try {
                        jQuery.post('[{$oViewConf->getSelfActionLink()}]', {
                            stoken: window._stoken,
                            cl: 'start',
                            fnc: 'tobasket',
                            sku: sku,
                            am: quantity
                        })
                        .done(resolve)
                        .fail(jqueryFail(reject));
                    } catch (error) {
                        reject(error);
                    }
                }).then(function() {
                    return new Promise(function(resolve, reject) {
                        jQuery.post('[{$oViewConf->getSelfActionLink()}]', {
                            cl: 'oxwMinibasket'
                        })
                        .done(function(data, status) {
                            try {
                                var result = $(data);
                                var element = $(result).first();

                                var ident = element.attr('id') ? ('#' + element.attr('id')) : '.' + element.attr('class').replace(" ", ".");

                                var selector = element.prop('tagName') + ident;

                                $(selector).replaceWith(data);
                            } catch (error) {
                                console.log(error);
                            }
                            resolve();
                        })
                        .fail(jqueryFail(reject));

                        if ( $('.shopping-bag-text').length > 0 ) {
                            jQuery.ajax({
                                url: '[{$oViewConf->getSelfActionLink()}]',
                                method: "POST",
                                data: {
                                    stoken: window._stoken,
                                    cl: 'oxwMiniBasket',
                                    fnc: 'getBasketItemsCount'
                                }
                            }).done(function(data) {
                                $('.shopping-bag-text,#countValue').html(data);
                            });
                        }
                    });
                });
            };
        </script>
    [{/if}]
[{/if}]

[{$smarty.block.parent}]
