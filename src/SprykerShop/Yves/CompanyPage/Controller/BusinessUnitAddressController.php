<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Controller;

use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use SprykerShop\Yves\CompanyPage\Form\CompanyBusinessUnitAddressForm;
use SprykerShop\Yves\CompanyPage\Plugin\Provider\CompanyPageControllerProvider;
use Symfony\Component\HttpFoundation\Request;

class BusinessUnitAddressController extends AbstractCompanyController
{
    public const REQUEST_COMPANY_BUSINESS_UNIT_ID = 'id';
    protected const REQUEST_PARAM_ID_COMPANY_BUSINESS_UNIT = 'idCompanyBusinessUnit';

    protected const MESSAGE_BUSINESS_UNIT_ADDRESS_CREATE_SUCCESS = 'message.business_unit_address.create';
    protected const MESSAGE_BUSINESS_UNIT_ADDRESS_UPDATE_SUCCESS = 'message.business_unit_address.update';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $response = $this->executeCreateAction($request);

        if (!is_array($response)) {
            return $response;
        }

        return $this->view($response, [], '@CompanyPage/views/business-unit-address-create/business-unit-address-create.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request)
    {
        $response = $this->executeUpdateAction($request);

        if (!is_array($response)) {
            return $response;
        }

        return $this->view($response, [], '@CompanyPage/views/business-unit-address-update/business-unit-address-update.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executeCreateAction(Request $request)
    {
        $idCompanyBusinessUnit = $request->query->getInt(static::REQUEST_COMPANY_BUSINESS_UNIT_ID);

        $dataProvider = $this
            ->getFactory()
            ->createCompanyPageFormFactory()
            ->createCompanyUnitAddressFormDataProvider();

        $addressForm = $this
            ->getFactory()
            ->createCompanyPageFormFactory()
            ->getCompanyBusinessUnitAddressForm($dataProvider->getOptions())
            ->handleRequest($request);

        if ($addressForm->isSubmitted() === false) {
            $addressForm->setData(
                $dataProvider->getData(
                    $this->findCurrentCompanyUserTransfer(),
                    null,
                    $idCompanyBusinessUnit
                )
            );
        }

        if ($addressForm->isValid()) {
            $data = $addressForm->getData();
            $data[CompanyUnitAddressTransfer::COMPANY_BUSINESS_UNITS][CompanyBusinessUnitCollectionTransfer::COMPANY_BUSINESS_UNITS][][CompanyBusinessUnitTransfer::ID_COMPANY_BUSINESS_UNIT] = $idCompanyBusinessUnit;

            $companyUnitAddressTransfer = $this->getFactory()
                ->createCompanyBusinessAddressSaver()
                ->saveAddress($data);

            if ($companyUnitAddressTransfer->getIdCompanyUnitAddress()) {
                $this->addTranslatedSuccessMessage(static::MESSAGE_BUSINESS_UNIT_ADDRESS_CREATE_SUCCESS, [
                    '%address%' => $companyUnitAddressTransfer->getAddress1(),
                ]);

                return $this->redirectResponseInternal(CompanyPageControllerProvider::ROUTE_COMPANY_BUSINESS_UNIT_UPDATE, [
                    'id' => $idCompanyBusinessUnit,
                ]);
            }
        }

        return [
            'form' => $addressForm->createView(),
            'idCompanyBusinessUnit' => $idCompanyBusinessUnit,
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function executeUpdateAction(Request $request)
    {
        $idCompanyUnitAddress = $request->query->getInt(static::REQUEST_COMPANY_BUSINESS_UNIT_ID);
        $idCompanyBusinessUnit = $request->query->getInt(static::REQUEST_PARAM_ID_COMPANY_BUSINESS_UNIT);

        $dataProvider = $this
            ->getFactory()
            ->createCompanyPageFormFactory()
            ->createCompanyUnitAddressFormDataProvider();

        $addressForm = $this
            ->getFactory()
            ->createCompanyPageFormFactory()
            ->getCompanyBusinessUnitAddressForm($dataProvider->getOptions())
            ->handleRequest($request);

        $data = $dataProvider->getData($this->findCurrentCompanyUserTransfer(), $idCompanyUnitAddress, $idCompanyBusinessUnit);

        if (!$this->isCurrentCustomerRelatedToCompany($data[CompanyBusinessUnitAddressForm::FIELD_FK_COMPANY])) {
            throw new NotFoundHttpException();
        }

        if ($addressForm->isValid()) {
            if (!$idCompanyBusinessUnit) {
                return $this->redirectResponseInternal(CompanyPageControllerProvider::ROUTE_COMPANY_BUSINESS_UNIT);
            }

            $data = array_merge($data, $addressForm->getData());

            $companyUnitAddressTransfer = $this->getFactory()
                ->createCompanyBusinessAddressSaver()
                ->saveAddress($data);

            $this->addTranslatedSuccessMessage(static::MESSAGE_BUSINESS_UNIT_ADDRESS_UPDATE_SUCCESS, [
                '%address%' => $companyUnitAddressTransfer->getAddress1(),
            ]);

            return $this->redirectResponseInternal(CompanyPageControllerProvider::ROUTE_COMPANY_BUSINESS_UNIT_UPDATE, [
                'id' => $idCompanyBusinessUnit,
            ]);
        }
        if ($addressForm->isSubmitted() === false) {
            $addressForm->setData($data);
        }

        return [
            'form' => $addressForm->createView(),
            'idCompanyBusinessUnit' => $idCompanyBusinessUnit,
            'idCompanyUnitAddress' => $idCompanyUnitAddress,
        ];
    }
}
