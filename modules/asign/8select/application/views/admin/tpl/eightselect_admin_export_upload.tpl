[{include file="headitem.tpl" box="export "
    title="GENERAL_ADMIN_TITLE"|oxmultilangassign
    meta_refresh_sec=$refresh
    meta_refresh_url=$oViewConf->getSelfLink()|cat:"&cl=`$sClassUpload`&fnc=run&`$sType`=true"
}]

[{ oxmultilang ident="mx_eightselect_admin_common" }] - [{ oxmultilang ident="mx_eightselect_admin_export" }] -

[{if !isset($refresh)}]
    [{if !isset($iError) }]
         [{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_UPLOAD_NOTSTARTED" }]
    [{else}]
        [{if $iError}]
            [{if $iError == -2}]
                [{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_UPLOAD_END" }]
                <b>[{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_UPLOAD_SUCCESS" }]</b>
            [{elseif $iError == -99}]
                [{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_NOFEEDID" }]
            [{elseif $iError == 1}]
                [{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_UPLOAD_NOFILE" }]
            [{else}]
                [{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_UPLOAD_UNKNOWNERROR" }]
            [{/if}]
        [{/if}]
    [{/if}]
[{else}]
  [{ oxmultilang ident="EIGHTSELECT_ADMIN_EXPORT_UPLOAD_RUNNING" }]
[{/if}]

[{include file="bottomitem.tpl"}]
