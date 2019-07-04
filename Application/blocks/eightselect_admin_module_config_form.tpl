[{if $_8select_connectError}]
    [{$_8select_connectError}]
[{elseif $_8select_connectSuccess}]
    [{$_8select_connectSuccess}]
[{/if}]

[{$smarty.block.parent}]

[{if $oModule->getInfo('id') === 'asign_8select'}]
    <input type="submit" class="confinput" name="save" value="[{ oxmultilang ident="mx_eightselect_connect_to_CSE" }]"
           onClick="Javascript:document.module_configuration.fnc.value='connectToCSE'" [{ $readonly }]>
[{/if}]
