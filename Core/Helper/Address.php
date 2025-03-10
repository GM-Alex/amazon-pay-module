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

namespace OxidProfessionalServices\AmazonPay\Core\Helper;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Application\Model\RequiredAddressFields;
use OxidProfessionalServices\AmazonPay\Core\Logger;
use OxidProfessionalServices\AmazonPay\Core\Config;
use VIISON\AddressSplitter\AddressSplitter;
use VIISON\AddressSplitter\Exceptions\SplittingException;

class Address
{
     /**
     * possible DBTable Prefix
     *
     * @var array
     */
    protected static $possibleDBTablePrefix = [
        'oxuser__' , 'oxaddress__'
    ];

     /**
     * possible DBTable Prefix
     *
     * @var string
     */
    protected static $defaultDBTablePrefix = 'oxaddress__';

    /**
     * This is used as a prefilter for OXID functions below.
     * @param $addr
     * @return array
     */
    public static function parseAddress(array $address): array
    {
        $name = trim($address['name']);
        $last_name = self::getLastName($name);
        $first_name = self::getFirstName($name);

        // Country
        $countryIsoCode = $address["countryCode"];
        $country = oxNew(Country::class);
        $countryOxId = $country->getIdByCode($countryIsoCode ?? '');
        $country->loadInLang(
            Registry::getLang()->getBaseLanguage(),
            $countryOxId
        );
        $countryName = $country->oxcountry__oxtitle->value;

        $company = '';
        $street = '';
        $streetNo = '';
        $additionalInfo = '';

        $addressData = null;

        $addressLines = self::getAddressLines($address);

        if ($countryIsoCode === 'DE' || $countryIsoCode === 'AT') {
            // special Amazon-Case: Street in first line, StreetNo in second line
            if (isset($addressLines[1]) && preg_match('/^\d.{0,8}$/', $addressLines[1])) {
                $streetTmp = $addressLines[0] . ' ' . $addressLines[1];
            // Company-Case: Company in first line Street and StreetNo in second line
            } elseif (isset($addressLines[1]) && $addressLines[1] != '') {
                $streetTmp = $addressLines[1];
                $company = $addressLines[0];
            // Normal-Case: No Company, Street & StreetNo in first line
            } else {
                $streetTmp = $addressLines[0];
            }
            if ($addressLines[2] != '') {
                $additionalInfo = $addressLines[2];
            }

            try {
                $addressData = AddressSplitter::splitAddress($streetTmp);
                $street = $addressData['streetName'] ?? '';
                $streetNo = $addressData['houseNumber'] ?? '';
            } catch (SplittingException $e) {
                // The Address could not be split
                // we have an exception, bit we did not log the message because of sensible Address-Informations
                // $logger = new Logger();
                // $logger->error($e->getMessage(), ['status' => $e->getCode()]);
                $street = $streetTmp;
            }
        } else {
            try {
                $addressLinesAsString = implode(', ', $addressLines);
                $addressData = AddressSplitter::splitAddress($addressLinesAsString);

                $company = $addressData['additionToAddress1'] ?? '';
                $street = $addressData['streetName'] ?? '';
                $streetNo = $addressData['houseNumber'] ?? '';
                $additionalInfo = $addressData['additionToAddress2'] ?? '';
            } catch (SplittingException $e) {
                // The Address could not be split
                // we have an exception, bit we did not log the message because of sensible Address-Informations
                // $logger = new Logger();
                // $logger->error($e->getMessage(), ['status' => $e->getCode()]);
                $street = $addressLinesAsString;
            }
        }

        return [
            'Firstname' => $first_name,
            'Lastname' => $last_name,
            'CountryIso' => $countryIsoCode,
            'CountryId' => $countryOxId,
            'Country' => $countryName,
            'Street' => $street,
            'StreetNo' => $streetNo,
            'AddInfo' => $additionalInfo,
            'Company' => $company,
            'PostalCode' => $address['postalCode'],
            'City' => $address['city'],
            'PhoneNumber' => $address['phoneNumber']
        ];
    }

    /**
     * @param array $address
     * @return array
     */
    public static function collectMissingRequiredBillingFields(array $address): array
    {
        $config = Registry::get(Config::class);

        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);
        $aRequiredBillingFields = $oRequiredAddressFields->getBillingFields();

        $missingFields = [];

        foreach ($aRequiredBillingFields as $billingKey) {
            if (
                (
                    isset($address[$billingKey]) &&
                    !$address[$billingKey]
                ) ||
                !isset($address[$billingKey])
            ) {
                // we collect the missing fields and filled as dummy with a Placeholder
                $missingFields[$billingKey] = $config->getPlaceholder();
            }
        }

        // Fix street, streetno missing field
        if (isset($missingFields['oxuser__oxstreet']) || isset($missingFields['oxuser__oxstreetnr'])) {
            $missingFields['oxuser__oxstreet'] = $config->getPlaceholder();
            $missingFields['oxuser__oxstreetnr'] = $config->getPlaceholder();
        }

        return $missingFields;
    }

    /**
     * @param array $address
     * @return array
     */
    public static function collectMissingRequiredDeliveryFields(array $address): array
    {
        $config = Registry::get(Config::class);

        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);
        $aRequiredDeliveryFields = $oRequiredAddressFields->getDeliveryFields();

        $missingFields = [];

        foreach ($aRequiredDeliveryFields as $deliveryKey) {
            if (
                (
                    isset($address[$deliveryKey]) &&
                    !$address[$deliveryKey]
                ) ||
                !isset($address[$deliveryKey])
            ) {
                // we collect the missing fields and filled as dummy with a Placeholder
                $missingFields[$deliveryKey] = $config->getPlaceholder();
            }
        }

        // Fix street, streetno missing field
        if (isset($missingFields['oxaddress__oxstreet']) || isset($missingFields['oxaddress__oxstreetnr'])) {
            $missingFields['oxaddress__oxstreet'] = $config->getPlaceholder();
            $missingFields['oxaddress__oxstreetnr'] = $config->getPlaceholder();
        }

        return $missingFields;
    }

    /**
     * @param array $address
     * @param string $DBTablePrefix
     * @return array
     */
    public static function mapAddressToDb(array $address, $DBTablePrefix): array
    {
        $DBTablePrefix = self::validateDBTablePrefix($DBTablePrefix);
        $parsedAddress = self::parseAddress($address);

        return [
            $DBTablePrefix . 'oxcompany' => $parsedAddress['Company'],
            $DBTablePrefix . 'oxfname' => $parsedAddress['Firstname'],
            $DBTablePrefix . 'oxlname' => $parsedAddress['Lastname'],
            $DBTablePrefix . 'oxstreet' => $parsedAddress['Street'],
            $DBTablePrefix . 'oxstreetnr' => $parsedAddress['StreetNo'],
            $DBTablePrefix . 'oxcity' => $parsedAddress['City'],
            $DBTablePrefix . 'oxcountryid' => $parsedAddress['CountryId'],
            $DBTablePrefix . 'oxcountry' => $parsedAddress['Country'],
            $DBTablePrefix . 'oxzip' => $parsedAddress['PostalCode'],
            $DBTablePrefix . 'oxfon' => $parsedAddress['PhoneNumber'],
            $DBTablePrefix . 'oxaddinfo' => $parsedAddress['AddInfo']
        ];
    }

    /**
     * Maps Amazon address fields to oxid fields
     *
     * @param array $address
     * @param string $DBTablePrefix
     *
     * @return array
     */
    public static function mapAddressToView(array $address, $DBTablePrefix): array
    {
        $config = Registry::get(Config::class);

        $DBTablePrefix = self::validateDBTablePrefix($DBTablePrefix);

        $parsedAddress = self::parseAddress($address);

        $result = [
            'oxcompany' => $parsedAddress['Company'],
            'oxfname' => $parsedAddress['Firstname'],
            'oxlname' => $parsedAddress['Lastname'],
            'oxstreet' => $parsedAddress['Street'],
            'oxstreetnr' => $parsedAddress['StreetNo'],
            'oxcity' => $parsedAddress['City'],
            'oxcountryid' => $parsedAddress['CountryId'],
            'oxcountry' => $parsedAddress['Country'],
            'oxstateid' => $address['stateOrRegion'],
            'oxzip' => $parsedAddress['PostalCode'],
            'oxfon' => $parsedAddress['PhoneNumber'],
            'oxaddinfo' => $parsedAddress['AddInfo'],
            'oxfax' => '',
            'oxsal' => ''
        ];

        $oRequiredAddressFields = oxNew(RequiredAddressFields::class);

        $aRequiredFields = $DBTablePrefix === 'oxuser__' ?
            $oRequiredAddressFields->getBillingFields() :
            $oRequiredAddressFields->getDeliveryFields();

        foreach ($aRequiredFields as $key) {
            $key = str_replace($DBTablePrefix, '', $key);
            if (
                (
                    isset($result[$key]) &&
                    !$result[$key]
                ) ||
                !isset($result[$key])
            ) {
                // we collect the missing fields and filled as dummy with a Placeholder
                $result[$key] = $config->getPlaceholder();
            }
        }

        return $result;
    }

    /**
     * Returns filled address lines from the address array
     *
     * @param array $address
     *
     * @return array
     */
    private static function getAddressLines(array $address): array
    {
        $lines = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($address["addressLine$i"]) && $address["addressLine$i"]) {
                $line = $address["addressLine$i"];
                preg_match_all('!\d+!', $line, $matches2);
                if (!empty($matches2[0])) {
                    $line = str_replace(implode(' ', $matches2[0]), implode(',', $matches2[0]), $line);
                }
                $lines[] = $line;
            }
        }

        return $lines;
    }

    /**
     * Firstname of a Name
     *
     * @param string
     *
     * @return string
     */
    private static function getFirstName($name)
    {
        return implode(' ', array_slice(explode(' ', $name), 0, -1));
    }

    /**
     * Lastname of a Name
     *
     * @param string
     *
     * @return string
     */
    private static function getLastName($name)
    {
        return array_slice(explode(' ', $name), -1)[0];
    }

    /**
     * validate the DBTablePrefix
     *
     * @param string $DBTablePrefix
     *
     * @return string
     */
    private static function validateDBTablePrefix($DBTablePrefix)
    {
        return in_array($DBTablePrefix, self::$possibleDBTablePrefix) ?
            $DBTablePrefix :
            self::$defaultDBTablePrefix;
    }
}
