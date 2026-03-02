<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Yves\Kernel\AbstractFactory;
use Spryker\Yves\Router\Router\ChainRouter;
use SprykerShop\Yves\CompanyPage\CustomerChecker\PreAuthUserChecker;
use SprykerShop\Yves\CompanyPage\CustomerChecker\PreAuthUserCheckerInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToBusinessOnBehalfClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyBusinessUnitClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyRoleClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyUnitAddressClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyUserClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCustomerClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToGlossaryStorageClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToLocaleClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToMessengerClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToPermissionClientInterface;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToStoreClientInterface;
use SprykerShop\Yves\CompanyPage\Expander\CompanyBusinessUnitOrderSearchFormExpander;
use SprykerShop\Yves\CompanyPage\Expander\CompanyBusinessUnitOrderSearchFormExpanderInterface;
use SprykerShop\Yves\CompanyPage\Expander\CompanyUnitAddressExpander;
use SprykerShop\Yves\CompanyPage\Expander\CompanyUnitAddressExpanderInterface;
use SprykerShop\Yves\CompanyPage\Form\Cloner\FormCloner;
use SprykerShop\Yves\CompanyPage\Form\Constraint\CompanyUserCustomerRelationConstraint;
use SprykerShop\Yves\CompanyPage\Form\Constraint\CompanyUserRelationConstraint;
use SprykerShop\Yves\CompanyPage\Form\DataProvider\CompanyBusinessUnitOrderSearchFormDataProvider;
use SprykerShop\Yves\CompanyPage\Form\FormFactory;
use SprykerShop\Yves\CompanyPage\FormHandler\OrderSearchFormHandler;
use SprykerShop\Yves\CompanyPage\FormHandler\OrderSearchFormHandlerInterface;
use SprykerShop\Yves\CompanyPage\Mapper\CompanyUnitMapper;
use SprykerShop\Yves\CompanyPage\Mapper\CompanyUnitMapperInterface;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressReader;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressReaderInterface;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressSaver;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressSaverInterface;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitTreeReader;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitTreeReaderInterface;
use SprykerShop\Yves\CompanyPage\Model\CompanyUser\CompanyUserSaver;
use SprykerShop\Yves\CompanyPage\Model\CompanyUser\CompanyUserSaverInterface;
use SprykerShop\Yves\CompanyPage\Model\CompanyUser\CompanyUserValidator;
use SprykerShop\Yves\CompanyPage\Model\CompanyUser\CompanyUserValidatorInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Validator\Constraint;

class CompanyPageFactory extends AbstractFactory
{
    public function createCompanyPageFormFactory(): FormFactory
    {
        return new FormFactory();
    }

    public function createCompanyBusinessUnitOrderSearchFormExpander(): CompanyBusinessUnitOrderSearchFormExpanderInterface
    {
        return new CompanyBusinessUnitOrderSearchFormExpander(
            $this->createCompanyBusinessUnitOrderSearchFormDataProvider(),
        );
    }

    public function createOrderSearchFormHandler(): OrderSearchFormHandlerInterface
    {
        return new OrderSearchFormHandler($this->getCustomerClient());
    }

    public function createCompanyBusinessUnitOrderSearchFormDataProvider(): CompanyBusinessUnitOrderSearchFormDataProvider
    {
        return new CompanyBusinessUnitOrderSearchFormDataProvider(
            $this->getCustomerClient(),
            $this->getCompanyBusinessUnitClient(),
        );
    }

    public function getCsrfTokenManager(): CsrfTokenManagerInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::SERVICE_FORM_CSRF_PROVIDER);
    }

    public function getCustomerClient(): CompanyPageToCustomerClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_CUSTOMER);
    }

    public function getStoreClient(): CompanyPageToStoreClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_STORE);
    }

    public function getCompanyUserClient(): CompanyPageToCompanyUserClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_COMPANY_USER);
    }

    public function getCompanyRoleClient(): CompanyPageToCompanyRoleClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_COMPANY_ROLE);
    }

    public function getCompanyClient(): CompanyPageToCompanyClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_COMPANY);
    }

    public function getCompanyBusinessUnitClient(): CompanyPageToCompanyBusinessUnitClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_COMPANY_BUSINESS_UNIT);
    }

    public function getCompanyOverviewWidgetPlugins(): array
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::PLUGIN_COMPANY_OVERVIEW_WIDGETS);
    }

    public function getCompanyUnitAddressClient(): CompanyPageToCompanyUnitAddressClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_COMPANY_UNIT_ADDRESS);
    }

    public function getPermissionClient(): CompanyPageToPermissionClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_PERMISSION);
    }

    public function getBusinessOnBehalfClient(): CompanyPageToBusinessOnBehalfClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_BUSINESS_ON_BEHALF);
    }

    public function getMessengerClient(): CompanyPageToMessengerClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_MESSENGER);
    }

    public function createCompanyUserSaver(): CompanyUserSaverInterface
    {
        return new CompanyUserSaver(
            $this->getMessengerClient(),
            $this->getCustomerClient(),
            $this->getBusinessOnBehalfClient(),
        );
    }

    public function createCompanyBusinessUnitTreeBuilder(): CompanyBusinessUnitTreeReaderInterface
    {
        return new CompanyBusinessUnitTreeReader(
            $this->getCustomerClient(),
            $this->getCompanyBusinessUnitClient(),
        );
    }

    public function getGlossaryStorageClient(): CompanyPageToGlossaryStorageClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_GLOSSARY_STORAGE);
    }

    public function getRouter(): ChainRouter
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::SERVICE_ROUTER);
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Service\Container\ContainerInterface
     */
    public function getApplication(): ContainerInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::PLUGIN_APPLICATION);
    }

    public function createCompanyBusinessAddressSaver(): CompanyBusinessUnitAddressSaverInterface
    {
        return new CompanyBusinessUnitAddressSaver(
            $this->getCompanyUnitAddressClient(),
        );
    }

    public function createCompanyBusinessUnitAddressReader(): CompanyBusinessUnitAddressReaderInterface
    {
        return new CompanyBusinessUnitAddressReader(
            $this->getCompanyUnitAddressClient(),
        );
    }

    public function createCompanyUserValidator(): CompanyUserValidatorInterface
    {
        return new CompanyUserValidator();
    }

    public function createCompanyUnitMapper(): CompanyUnitMapperInterface
    {
        return new CompanyUnitMapper();
    }

    public function createCompanyUnitAddressExpander(): CompanyUnitAddressExpanderInterface
    {
        return new CompanyUnitAddressExpander($this->createCompanyUnitMapper());
    }

    public function createFormCloner(): FormCloner
    {
        return new FormCloner();
    }

    public function createPreAuthUserChecker(): PreAuthUserCheckerInterface
    {
        return new PreAuthUserChecker();
    }

    public function createCompanyUserRelationConstraint(): Constraint
    {
        return new CompanyUserRelationConstraint([
            CompanyUserRelationConstraint::OPTION_CUSTOMER_CLIENT => $this->getCustomerClient(),
            CompanyUserRelationConstraint::OPTION_GLOSSARY_STORAGE_CLIENT => $this->getGlossaryStorageClient(),
            CompanyUserRelationConstraint::OPTION_LOCALE_CLIENT => $this->getLocaleClient(),
        ]);
    }

    public function createCompanyUserCustomerRelationConstraint(): Constraint
    {
        return new CompanyUserCustomerRelationConstraint([
            CompanyUserRelationConstraint::OPTION_CUSTOMER_CLIENT => $this->getCustomerClient(),
            CompanyUserRelationConstraint::OPTION_GLOSSARY_STORAGE_CLIENT => $this->getGlossaryStorageClient(),
            CompanyUserRelationConstraint::OPTION_LOCALE_CLIENT => $this->getLocaleClient(),
        ]);
    }

    public function getLocaleClient(): CompanyPageToLocaleClientInterface
    {
        return $this->getProvidedDependency(CompanyPageDependencyProvider::CLIENT_LOCALE);
    }
}
