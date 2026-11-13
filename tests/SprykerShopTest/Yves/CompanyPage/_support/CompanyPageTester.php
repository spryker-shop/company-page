<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CompanyPage;

use Codeception\Actor;
use Codeception\Stub;
use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerShop\Yves\CompanyPage\CompanyPageConfig;
use SprykerShop\Yves\CompanyPage\CompanyPageFactory;
use SprykerShop\Yves\CompanyPage\Controller\AbstractCompanyController;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyBusinessUnitClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyUnitAddressClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCustomerClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToGlossaryStorageClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToLocaleClientInterface;
use SprykerShop\Yves\CompanyPage\Form\CompanyUnitAddressForm;
use SprykerShop\Yves\CompanyPage\Form\Constraint\CompanyUserRelationConstraint;
use SprykerShop\Yves\CompanyPage\Form\DataProvider\CompanyUnitAddressFormDataProvider;
use SprykerShop\Yves\CompanyPage\Form\FormFactory;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressSaverInterface;
use SprykerShop\Yves\ShopUi\Dependency\Service\ShopUiToUtilSanitizeXssServiceInterface;
use SprykerShop\Yves\ShopUi\Form\Type\Extension\SanitizeXssTypeExtension;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Validation;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class CompanyPageTester extends Actor
{
    use _generated\CompanyPageTesterActions;

    public const int ID_COMPANY_SESSION = 1;

    public const int ID_COMPANY_OTHER = 2;

    public const int ID_COMPANY_BUSINESS_UNIT_OWN = 5;

    public const int ID_COMPANY_BUSINESS_UNIT_OTHER = 6;

    public const int ID_COMPANY_UNIT_ADDRESS_OWN = 11;

    public const int ID_COMPANY_UNIT_ADDRESS_OTHER = 22;

    protected const array COMPANY_BY_ID_COMPANY_UNIT_ADDRESS = [
        self::ID_COMPANY_UNIT_ADDRESS_OWN => self::ID_COMPANY_SESSION,
        self::ID_COMPANY_UNIT_ADDRESS_OTHER => self::ID_COMPANY_OTHER,
    ];

    protected const string COUNTRY_ISO2_CODE = 'DE';

    /**
     * @template T of \SprykerShop\Yves\CompanyPage\Controller\AbstractCompanyController
     *
     * @param class-string<T> $controllerClassName
     *
     * @return T
     */
    public function createCompanyControllerStub(
        string $controllerClassName,
        CompanyPageFactory $companyPageFactory,
        bool $hasCurrentCompanyUser = true
    ): AbstractCompanyController {
        $currentCompanyUserTransfer = $hasCurrentCompanyUser ? $this->createSessionCompanyUserTransfer() : null;

        return Stub::make($controllerClassName, [
            'getFactory' => $companyPageFactory,
            'findCurrentCompanyUserTransfer' => function () use ($currentCompanyUserTransfer): ?CompanyUserTransfer {
                return $currentCompanyUserTransfer;
            },
            'addTranslatedSuccessMessage' => function (): void {
            },
            'redirectResponseInternal' => new RedirectResponse('/'),
        ]);
    }

    public function createCompanyPageFactoryStub(
        FormFactory $formFactory,
        CompanyPageToCompanyUnitAddressClientInterface $companyUnitAddressClient,
        CompanyBusinessUnitAddressSaverInterface $companyBusinessUnitAddressSaver
    ): CompanyPageFactory {
        return Stub::make(CompanyPageFactory::class, [
            'createCompanyPageFormFactory' => $formFactory,
            'getCompanyUnitAddressClient' => $companyUnitAddressClient,
            'getCompanyBusinessUnitClient' => $this->createCompanyBusinessUnitClientStub(),
            'createCompanyBusinessAddressSaver' => $companyBusinessUnitAddressSaver,
        ]);
    }

    public function createFormFactoryStub(FormInterface $form): FormFactory
    {
        return Stub::makeEmpty(FormFactory::class, [
            'createCompanyUnitAddressFormDataProvider' => Stub::makeEmpty(CompanyUnitAddressFormDataProvider::class, [
                'getOptions' => [],
                'getData' => [],
            ]),
            'getCompanyUnitAddressForm' => $form,
            'getCompanyBusinessUnitAddressForm' => $form,
        ]);
    }

    public function createFormStub(int $idSubmittedCompanyUnitAddress, bool $isSubmitted = true): FormInterface
    {
        $form = Stub::makeEmpty(FormInterface::class, [
            'isSubmitted' => $isSubmitted,
            'isValid' => true,
            'getData' => $this->createTamperedCompanyUnitAddressFormData($idSubmittedCompanyUnitAddress),
            'createView' => new FormView(),
        ]);
        $form->method('handleRequest')->willReturn($form);

        return $form;
    }

    /**
     * @return array<string, mixed>
     */
    public function createTamperedCompanyUnitAddressFormData(int $idSubmittedCompanyUnitAddress): array
    {
        return [
            CompanyUnitAddressForm::FIELD_ID_COMPANY_UNIT_ADDRESS => (string)$idSubmittedCompanyUnitAddress,
            CompanyUnitAddressForm::FIELD_FK_COMPANY => static::ID_COMPANY_OTHER,
            CompanyUnitAddressForm::FIELD_FK_COMPANY_BUSINESS_UNIT => static::ID_COMPANY_BUSINESS_UNIT_OTHER,
        ];
    }

    public function configureCompanyUnitAddressClientMock(
        CompanyPageToCompanyUnitAddressClientInterface&MockObject $companyUnitAddressClientMock
    ): CompanyPageToCompanyUnitAddressClientInterface&MockObject {
        $companyUnitAddressClientMock->method('getCompanyUnitAddressById')
            ->willReturnCallback(function (CompanyUnitAddressTransfer $companyUnitAddressTransfer): CompanyUnitAddressTransfer {
                return $companyUnitAddressTransfer->setFkCompany(
                    static::COMPANY_BY_ID_COMPANY_UNIT_ADDRESS[$companyUnitAddressTransfer->getIdCompanyUnitAddress()],
                );
            });

        return $companyUnitAddressClientMock;
    }

    public function createRealCompanyUnitAddressForm(): FormInterface
    {
        $companyUnitAddressForm = new CompanyUnitAddressForm();
        $companyUnitAddressForm->setFactory(Stub::make(CompanyPageFactory::class, [
            'createCompanyUserRelationConstraint' => $this->createCompanyUserRelationConstraint(),
        ]));
        $companyUnitAddressForm->setConfig(new CompanyPageConfig());

        return Forms::createFormFactoryBuilder()
            ->addExtension(new HttpFoundationExtension())
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addTypeExtension(new SanitizeXssTypeExtension(Stub::makeEmpty(ShopUiToUtilSanitizeXssServiceInterface::class, [
                'sanitizeXss' => function (string $text): string {
                    return $text;
                },
            ])))
            ->addType($companyUnitAddressForm)
            ->getFormFactory()
            ->create(CompanyUnitAddressForm::class, null, [
                CompanyUnitAddressForm::OPTION_COUNTRY_CHOICES => [static::COUNTRY_ISO2_CODE => static::COUNTRY_ISO2_CODE],
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function createValidCompanyUnitAddressFormData(int $idCompany): array
    {
        return [
            CompanyUnitAddressForm::FIELD_ID_COMPANY_UNIT_ADDRESS => (string)static::ID_COMPANY_UNIT_ADDRESS_OTHER,
            CompanyUnitAddressForm::FIELD_FK_COMPANY => (string)$idCompany,
            CompanyUnitAddressForm::FIELD_FK_COMPANY_BUSINESS_UNIT => (string)static::ID_COMPANY_BUSINESS_UNIT_OTHER,
            CompanyUnitAddressForm::FIELD_ADDRESS_1 => 'Main street',
            CompanyUnitAddressForm::FIELD_ADDRESS_2 => '12',
            CompanyUnitAddressForm::FIELD_ZIP_CODE => '10115',
            CompanyUnitAddressForm::FIELD_CITY => 'Berlin',
            CompanyUnitAddressForm::FIELD_ISO_2_CODE => static::COUNTRY_ISO2_CODE,
        ];
    }

    protected function createSessionCompanyUserTransfer(): CompanyUserTransfer
    {
        return (new CompanyUserTransfer())->setFkCompany(static::ID_COMPANY_SESSION);
    }

    protected function createCompanyUserRelationConstraint(): CompanyUserRelationConstraint
    {
        return new CompanyUserRelationConstraint([
            CompanyUserRelationConstraint::OPTION_CUSTOMER_CLIENT => Stub::makeEmpty(CompanyPageToCustomerClientInterface::class, [
                'getCustomer' => (new CustomerTransfer())->setCompanyUserTransfer($this->createSessionCompanyUserTransfer()),
            ]),
            CompanyUserRelationConstraint::OPTION_GLOSSARY_STORAGE_CLIENT => Stub::makeEmpty(CompanyPageToGlossaryStorageClientInterface::class),
            CompanyUserRelationConstraint::OPTION_LOCALE_CLIENT => Stub::makeEmpty(CompanyPageToLocaleClientInterface::class),
        ]);
    }

    protected function createCompanyBusinessUnitClientStub(): CompanyPageToCompanyBusinessUnitClientInterface
    {
        return Stub::makeEmpty(CompanyPageToCompanyBusinessUnitClientInterface::class, [
            'getCompanyBusinessUnitCollection' => function (
                CompanyBusinessUnitCriteriaFilterTransfer $companyBusinessUnitCriteriaFilterTransfer
            ): CompanyBusinessUnitCollectionTransfer {
                $companyBusinessUnitCollectionTransfer = new CompanyBusinessUnitCollectionTransfer();

                if (
                    $companyBusinessUnitCriteriaFilterTransfer->getIdCompany() !== static::ID_COMPANY_SESSION
                    || $companyBusinessUnitCriteriaFilterTransfer->getCompanyBusinessUnitIds() !== [static::ID_COMPANY_BUSINESS_UNIT_OWN]
                ) {
                    return $companyBusinessUnitCollectionTransfer;
                }

                return $companyBusinessUnitCollectionTransfer->addCompanyBusinessUnit(
                    (new CompanyBusinessUnitTransfer())->setIdCompanyBusinessUnit(static::ID_COMPANY_BUSINESS_UNIT_OWN),
                );
            },
        ]);
    }
}
