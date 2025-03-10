[{assign var="missingRequiredBillingFields" value=$oView->getMissingRequiredBillingFields()}]
[{if $missingRequiredBillingFields|@count}]
    [{foreach from=$missingRequiredBillingFields item=missingFieldValue key=missingFieldName}]
        <input type="hidden" name="missing_amazon_invadr[[{$missingFieldName}]]" value="" />
    [{/foreach}]
[{/if}]
[{assign var="missingRequiredDeliveryFields" value=$oView->getMissingRequiredDeliveryFields()}]
[{if $missingRequiredDeliveryFields|@count}]
    [{foreach from=$missingRequiredDeliveryFields item=missingFieldValue key=missingFieldName}]
        <input type="hidden" name="missing_amazon_deladr[[{$missingFieldName}]]" value="" />
    [{/foreach}]
[{/if}]
[{capture name="amazonpay_missingfields_script"}]
    $("#orderConfirmAgbBottom").submit(function(event) {
        var dontStopSubmit = true;
        $('#missing_delivery_address [id^=missing_amazon_deladr]').each(function(index) {
            if (!$(this).val()) {
                dontStopSubmit = false;
            }
            $('#orderConfirmAgbBottom input[name="' + $(this).attr("name") + '"]').val($(this).val());
        });
        $('#missing_billing_address [id^=missing_amazon_invadr]').each(function(index) {
            if (!$(this).val()) {
                dontStopSubmit = false;
            }
            $('#orderConfirmAgbBottom input[name="' + $(this).attr("name") + '"]').val($(this).val());
        });
        return dontStopSubmit;
    });
[{/capture}]
[{oxscript add=$smarty.capture.amazonpay_missingfields_script}]

[{if !$oViewConf->isAmazonSessionActive() && !$oViewConf->isAmazonExclude()}]
    <div class="float-right">
        [{include file="amazonpay/amazonbutton.tpl" buttonId="AmazonPayButtonNextCart2"}]
    </div>
    <div class="float-right amazonpay-button-or">
        [{"OR"|oxmultilangassign|oxupper}]
    </div>
[{/if}]