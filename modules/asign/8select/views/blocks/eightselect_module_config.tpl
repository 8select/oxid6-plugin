
[{if $module_var === 'sArticleSkuField' || $module_var === 'sArticleColorField'}]
    <select class="select" name="confselects[[{$module_var}]]" [{ $readonly }]>
        [{foreach from=$oView->getEightSelectFields() item='aFields' key='sGroup'}]
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
