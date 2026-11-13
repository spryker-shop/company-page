<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Controller;

use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressResponseTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use SprykerShop\Yves\CompanyPage\Form\CompanyBusinessUnitAddressForm;
use SprykerShop\Yves\CompanyPage\Plugin\Router\CompanyPageRouteProviderPlugin;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class BusinessUnitAddressController extends AbstractCompanyController
{
    /**
     * @var string
     */
    public const REQUEST_COMPANY_BUSINESS_UNIT_ID = 'id';

    /**
     * @var string
     */
    protected const REQUEST_PARAM_ID_COMPANY_BUSINESS_UNIT = 'idCompanyBusinessUnit';

    /**
     * @var string
     */
    protected const MESSAGE_BUSINESS_UNIT_ADDRESS_CREATE_SUCCESS = 'message.business_unit_address.create';

    /**
     * @var string
     */
    protected const MESSAGE_BUSINESS_UNIT_ADDRESS_UPDATE_SUCCESS = 'message.business_unit_address.update';

    /**
     * @var string
     */
    protected const GLOSSARY_MESSAGE_PARAM_ADDRESS = '%address%';

    /**
     * @deprecated Use {@link \SprykerShop\Yves\CompanyPage\Plugin\Router\CompanyPageRouteProviderPlugin::ROUTE_NAME_COMPANY_BUSINESS_UNIT} instead.
     *
     * @var string
     */
    protected const ROUTE_COMPANY_BUSINESS_UNIT = 'company/business-unit';

    /**
     * @deprecated Use {@link \SprykerShop\Yves\CompanyPage\Plugin\Router\CompanyPageRouteProviderPlugin::ROUTE_NAME_COMPANY_BUSINESS_UNIT_UPDATE} instead.
     *
     * @var string
     */
    protected const ROUTE_COMPANY_BUSINESS_UNIT_UPDATE = 'company/business-unit/update';

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
     * @return \Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse|array
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    protected function executeCreateAction(Request $request)
    {
        $idCompany = $this->getCurrentCompanyIdOrFail();

        $dataProvider = $this
            ->getFactory()
            ->createCompanyPageFormFactory()
            ->createCompanyUnitAddressFormDataProvider();

        $addressForm = $this
            ->getFactory()
            ->createCompanyPageFormFactory()
            ->getCompanyBusinessUnitAddressForm($dataProvider->getOptions())
            ->handleRequest($request);

        $idCompanyBusinessUnit = $request->query->getInt(static::REQUEST_COMPANY_BUSINESS_UNIT_ID);
        $this->assertCompanyBusinessUnitRelatedToCurrentCompany($idCompanyBusinessUnit);

        if ($addressForm->isSubmitted() === false) {
            $addressForm->setData(
                $dataProvider->getData(
                    $this->findCurrentCompanyUserTransfer(),
                    null,
                    $idCompanyBusinessUnit,
                ),
            );
        }

        if ($addressForm->isSubmitted() === true && $addressForm->isValid() === true) {
            $data = $this->mapCompanyUnitAddressIdentifiers($addressForm->getData(), null, $idCompanyBusinessUnit, $idCompany);
            $data[CompanyUnitAddressTransfer::COMPANY_BUSINESS_UNITS][CompanyBusinessUnitCollectionTransfer::COMPANY_BUSINESS_UNITS][][CompanyBusinessUnitTransfer::ID_COMPANY_BUSINESS_UNIT] = $idCompanyBusinessUnit;

            $companyUnitAddressTransfer = $this->getFactory()
                ->createCompanyBusinessAddressSaver()
                ->saveAddress($data);

            if ($companyUnitAddressTransfer->getIdCompanyUnitAddress()) {
                $this->addTranslatedSuccessMessage(static::MESSAGE_BUSINESS_UNIT_ADDRESS_CREATE_SUCCESS, [
                    '%address%' => $companyUnitAddressTransfer->getAddress1(),
                ]);

                return $this->redirectResponseInternal(CompanyPageRouteProviderPlugin::ROUTE_NAME_COMPANY_BUSINESS_UNIT_UPDATE, [
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|array
     */
    protected function executeUpdateAction(Request $request)
    {
        $idCompany = $this->getCurrentCompanyIdOrFail();
        $idCompanyUnitAddress = $request->query->getInt(static::REQUEST_COMPANY_BUSINESS_UNIT_ID);
        $idCompanyBusinessUnit = $request->query->getInt(static::REQUEST_PARAM_ID_COMPANY_BUSINESS_UNIT);

        if (!$idCompanyBusinessUnit || !$idCompanyUnitAddress) {
            return $this->redirectResponseInternal(CompanyPageRouteProviderPlugin::ROUTE_NAME_COMPANY_BUSINESS_UNIT);
        }

        $this->assertCompanyUnitAddressRelatedToCurrentCompany($idCompanyUnitAddress);
        $this->assertCompanyBusinessUnitRelatedToCurrentCompany($idCompanyBusinessUnit);

        $companyBusinessUnitAddressForm = $this->getCompanyBusinessUnitAddressForm($idCompanyUnitAddress, $idCompanyBusinessUnit)
            ->handleRequest($request);

        if ($companyBusinessUnitAddressForm->isSubmitted() && $companyBusinessUnitAddressForm->isValid()) {
            $companyUnitAddressResponseTransfer = $this->updateCompanyBusinessUnitAddress($companyBusinessUnitAddressForm, $idCompanyBusinessUnit, $idCompany);

            if ($companyUnitAddressResponseTransfer->getIsSuccessful()) {
                $this->addTranslatedSuccessMessage(static::MESSAGE_BUSINESS_UNIT_ADDRESS_UPDATE_SUCCESS, [
                    static::GLOSSARY_MESSAGE_PARAM_ADDRESS => $companyUnitAddressResponseTransfer->getCompanyUnitAddressTransfer()->getAddress1(),
                ]);

                return $this->redirectResponseInternal(CompanyPageRouteProviderPlugin::ROUTE_NAME_COMPANY_BUSINESS_UNIT_UPDATE, [
                    static::REQUEST_COMPANY_BUSINESS_UNIT_ID => $idCompanyBusinessUnit,
                ]);
            }

            $this->addCompanyUnitAddressResponseErrorMessages($companyUnitAddressResponseTransfer);
        }

        return [
            'form' => $companyBusinessUnitAddressForm->createView(),
            'idCompanyBusinessUnit' => $idCompanyBusinessUnit,
            'idCompanyUnitAddress' => $idCompanyUnitAddress,
        ];
    }

    protected function updateCompanyBusinessUnitAddress(
        FormInterface $companyBusinessUnitAddressForm,
        int $idCompanyBusinessUnit,
        int $idCompany
    ): CompanyUnitAddressResponseTransfer {
        $companyUnitAddressData = $companyBusinessUnitAddressForm->getData();
        $idCompanyUnitAddress = (int)$companyUnitAddressData[CompanyBusinessUnitAddressForm::FIELD_ID_COMPANY_UNIT_ADDRESS];
        $this->assertCompanyUnitAddressRelatedToCurrentCompany($idCompanyUnitAddress);

        $companyUnitAddressData = $this->mapCompanyUnitAddressIdentifiers($companyUnitAddressData, $idCompanyUnitAddress, $idCompanyBusinessUnit, $idCompany);

        return $this->getFactory()
            ->getCompanyUnitAddressClient()
            ->updateCompanyUnitAddress((new CompanyUnitAddressTransfer())->fromArray($companyUnitAddressData));
    }

    protected function addCompanyUnitAddressResponseErrorMessages(CompanyUnitAddressResponseTransfer $companyUnitAddressResponseTransfer): void
    {
        foreach ($companyUnitAddressResponseTransfer->getMessages() as $responseMessageTransfer) {
            $this->addErrorMessage($responseMessageTransfer->getText());
        }
    }

    protected function getCompanyBusinessUnitAddressForm(int $idCompanyUnitAddress, int $idCompanyBusinessUnit): FormInterface
    {
        $companyUnitAddressFormDataProvider = $this->getFactory()
            ->createCompanyPageFormFactory()
            ->createCompanyUnitAddressFormDataProvider();

        $companyUnitAddressFormData = $companyUnitAddressFormDataProvider->getData(
            $this->findCurrentCompanyUserTransfer(),
            $idCompanyUnitAddress,
            $idCompanyBusinessUnit,
        );

        return $this->getFactory()
            ->createCompanyPageFormFactory()
            ->getCompanyBusinessUnitAddressForm(
                $companyUnitAddressFormDataProvider->getOptions(),
                $companyUnitAddressFormData,
            );
    }
}
