
[{if $module_var === 'sArticleSkuField'}]
    <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
        [{foreach from=$oView->getEightSelectSkuFields() item='sFieldTitle' key='sField'}]
            <option value="[{$sField|escape}]" [{if ($confselects.$module_var==$sField)}]selected[{/if}]>[{$sFieldTitle}]</option>
        [{/foreach}]
    </select>
[{elseif $module_var === 'sArticleColorField'}]
    <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
        [{foreach from=$oView->getEightSelectColorFields() item='aFields' key='sGroup'}]
            <optgroup label="[{$sGroup}]">
                [{foreach from=$aFields key='sField' item='sFieldTitle'}]
                    <option value="[{$sField|escape}]"  [{if ($confselects.$module_var==$sField)}]selected[{/if}]>[{$sFieldTitle}]</option>
                [{/foreach}]
            </optgroup>
        [{/foreach}]
    </select>
[{else}]
    [{$smarty.block.parent}]
[{/if}]
