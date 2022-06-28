[{if $billadr}]
    <div class="col-lg-9 offset-lg-3" id="addressText">
        [{if $billadr->oxuser__oxcompany->value != ''}] [{$billadr->oxuser__oxcompany->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxaddinfo->value != ''}] [{$billadr->oxuser__oxaddinfo->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxustid->value != ''}] [{oxmultilang ident="VAT_ID_NUMBER"}] [{$billadr->oxuser__oxustid->value}]<br /> [{/if}]
        [{if $billadr->oxuser__oxfname->value != '' || $billadr->oxuser__oxlname->value != ''}]
            [{if $billadr->oxuser__oxsal->value != ''}][{$billadr->oxuser__oxsal->value|oxmultilangsal}]&nbsp;[{/if}][{$billadr->oxuser__oxfname->value}] [{$billadr->oxuser__oxlname->value}]<br />
        [{/if}]
        [{if $billadr->oxuser__oxstreet->value != '' || $billadr->oxuser__oxstreetnr->value != ''}][{$billadr->oxuser__oxstreet->value}]&nbsp;[{$billadr->oxuser__oxstreetnr->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxstateid->value != ''}][{$billadr->oxuser__oxstateid->value != ''}]&nbsp;[{/if}]
        [{if $billadr->oxuser__oxzip->value || $billadr->oxuser__oxcity->value}][{$billadr->oxuser__oxzip->value}]&nbsp;[{$billadr->oxuser__oxcity->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxcountry->value != ''}][{$billadr->oxuser__oxcountry->value}]<br /><br />[{/if}]
        [{if $billadr->oxuser__oxusername->value != ''}]<strong>[{oxmultilang ident="EMAIL"}]</strong> [{$billadr->oxuser__oxusername->value}]<br /><br />[{/if}]
        [{if $billadr->oxuser__oxfon->value != ''}]<strong>[{oxmultilang ident="PHONE"}]</strong> [{$billadr->oxuser__oxfon->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxfax->value != ''}]<strong>[{oxmultilang ident="FAX"}]</strong> [{$billadr->oxuser__oxfax->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxmobfon->value != ''}]<strong>[{oxmultilang ident="CELLUAR_PHONE"}]</strong> [{$billadr->oxuser__oxmobfon->value}]<br />[{/if}]
        [{if $billadr->oxuser__oxprivfon->value != ''}]<strong>[{oxmultilang ident="PERSONAL_PHONE"}]</strong> [{$billadr->oxuser__oxprivfon->value}]<br />[{/if}]
    </div>
[{/if}]


