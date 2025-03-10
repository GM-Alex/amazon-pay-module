<?php

/**
 * This file is part of OXID eSales AmazonPay module.
 *
 * OXID eSales AmazonPay module is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * OXID eSales AmazonPay module is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OXID eSales AmazonPay module.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link      http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2020
 */

namespace OxidProfessionalServices\AmazonPay\Component;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\PaymentList;
use OxidEsales\Eshop\Application\Model\DeliverySetList;
use OxidProfessionalServices\AmazonPay\Core\Helper\Address;
use OxidProfessionalServices\AmazonPay\Core\Provider\OxidServiceProvider;

/**
 * Handles Amazon checkout sessions
 * @mixin \OxidEsales\Eshop\Application\Component\UserComponent
 */
class UserComponent extends UserComponent_Parent
{
    /**
     * @param array $amazonSession
     */
    public function createGuestUser(array $amazonSession): void
    {
        $session = Registry::getSession();

        $this->setParent(oxNew('Register'));

        $this->setRequestParameter('userLoginName', $amazonSession['response']['buyer']['name']);
        $this->setRequestParameter('lgn_usr', $amazonSession['response']['buyer']['email']);

        // Guest users have a blank password
        $password = '';
        $this->setRequestParameter('lgn_pwd', $password);
        $this->setRequestParameter('lgn_pwd2', $password);
        $this->setRequestParameter('lgn_pwd2', $password);

        $mappedBillingFields = Address::mapAddressToDb($amazonSession['response']['billingAddress'], 'oxuser__');
        $mappedDeliveryFields = Address::mapAddressToDb($amazonSession['response']['shippingAddress'], 'oxaddress__');
        $missingBillingFields = Address::collectMissingRequiredBillingFields($mappedBillingFields);
        $missingDeliveryFields = Address::collectMissingRequiredDeliveryFields($mappedDeliveryFields);

        $this->deleteMissingSession();

        if (count($missingBillingFields)) {
            $session->setVariable('amazonMissingBillingFields', $missingBillingFields);
        }

        if (count($missingDeliveryFields)) {
            $session->setVariable('amazonMissingDeliveryFields', $missingDeliveryFields);
        }

        $billingAddress = array_merge($mappedBillingFields, $missingBillingFields);
        $deliveryAddress = array_merge($mappedDeliveryFields, $missingDeliveryFields);

        $this->setRequestParameter('invadr', $billingAddress);
        $this->setRequestParameter('deladr', $deliveryAddress);
        $session->setVariable('amazondeladr', $deliveryAddress);

        $registrationResult = $this->registerUser();

        if ($registrationResult) {
            $basket = $session->getBasket();
            $user = $this->getUser();
            $countryOxId = $mappedDeliveryFields['oxaddress__oxcountryid'] ?? $user->getActiveCountry();

            $deliverySetList = Registry::get(DeliverySetList::class)
            ->getDeliverySetList(
                $user,
                $countryOxId
            );
            foreach ($deliverySetList as $deliverySet) {
                $paymentList = Registry::get(PaymentList::class)->getPaymentList(
                    $deliverySet->getId(),
                    $basket->getPrice()->getBruttoPrice(),
                    $user
                );
                if (array_key_exists('oxidamazon', $paymentList)) {
                    $possibleDeliverySets[] = $deliverySet->getId();
                }
            }

            if (count($possibleDeliverySets)) {
                $basket->setPayment('oxidamazon');
                $basket->setShipping(reset($possibleDeliverySets));
            }
        } else {
            Registry::getUtils()->redirect(Registry::getConfig()->getShopHomeUrl() . 'cl=user', false, 302);
        }
    }

    /**
     * @param string $paramName
     * @param mixed $paramValue
     */
    public function setRequestParameter(string $paramName, $paramValue): void
    {
        $_POST[$paramName] = $paramValue;
    }

    /**
     * Deletes user information from session:<br>
     * "usr", "dynvalue", "paymentid"<br>
     * also deletes cookie, unsets \OxidEsales\Eshop\Core\Config::oUser,
     * oxcmp_user::oUser, forces basket to recalculate.
     *
     * @return null
     */
    public function logout()
    {
        // destroy Amazon Session
        OxidServiceProvider::getAmazonService()->unsetPaymentMethod();
        $this->deleteMissingSession();
        parent::logout();
    }

    /**
     * Deletes Missing Session Items
     *
     * @return null
     */
    protected function deleteMissingSession()
    {
        // delete Session-Items
        $session = Registry::getSession();
        $session->deleteVariable('amazonMissingBillingFields');
        $session->deleteVariable('amazonMissingDeliveryFields');
    }

    /**
     * Returns delivery address from request. Before returning array is checked if
     * all needed data is there
     *
     * @return array
     * @deprecated underscore prefix violates PSR12, will be renamed to "getDelAddressData" in next major
     */
    protected function _getDelAddressData() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $session = Registry::getSession();
        if ($session->getVariable('paymentid') !== 'oxidamazon') {
            return parent::_getDelAddressData();
        }
        $aDelAdress = [];
        $aDeladr = $session->getVariable('amazondeladr');
        if (count($aDeladr)) {
            $aDelAdress = $aDeladr;
        }
        return $aDelAdress;
    }
}
