<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidSolutionCatalysts\AmazonPay\Controller;

use OxidEsales\Eshop\Application\Controller\FrontendController;
use OxidSolutionCatalysts\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * handles amazon checkout ajax calls
 */
class AmazonCheckoutAjaxController extends FrontendController
{
    public function confirmAGB()
    {
        $conditionsService = OxidServiceProvider::getTermsAndConditionService();
        $conditionsService->setConfirmFromRequestToSession();

        $this->_aViewData['jsonResponse'] = json_encode(['success' => true]);
    }

    public function render()
    {
        return 'amazonpay/json.tpl';
    }
}
