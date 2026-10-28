<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CompanyPage\Controller;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyBusinessUnitCollectionTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use SprykerShop\Yves\CompanyPage\CompanyPageFactory;
use SprykerShop\Yves\CompanyPage\Controller\BusinessUnitController;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyBusinessUnitClientBridge;
use SprykerShop\Yves\CompanyPage\Form\DataProvider\CompanyBusinessUnitFormDataProvider;
use SprykerShop\Yves\CompanyPage\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CompanyPage
 * @group Controller
 * @group BusinessUnitControllerTest
 */
class BusinessUnitControllerTest extends Unit
{
    protected const int ID_COMPANY_SESSION = 1;

    protected const int ID_COMPANY_BUSINESS_UNIT_OWN = 31;

    protected const int ID_COMPANY_BUSINESS_UNIT_NOT_IN_COMPANY = 99;

    /**
     * @var \SprykerShopTest\Yves\CompanyPage\CompanyPageTester
     */
    protected $tester;

    public function testFindCompanyBusinessUnitForCurrentCompanyFiltersBySessionCompany(): void
    {
        // Arrange
        $companyBusinessUnitClientMock = $this->createCompanyBusinessUnitClientMock();
        $companyBusinessUnitClientMock->expects($this->once())
            ->method('getCompanyBusinessUnitCollection')
            ->with($this->callback(function (CompanyBusinessUnitCriteriaFilterTransfer $companyBusinessUnitCriteriaFilterTransfer): bool {
                return $companyBusinessUnitCriteriaFilterTransfer->getIdCompany() === static::ID_COMPANY_SESSION
                    && $companyBusinessUnitCriteriaFilterTransfer->getCompanyBusinessUnitIds() === [static::ID_COMPANY_BUSINESS_UNIT_OWN];
            }))
            ->willReturn((new CompanyBusinessUnitCollectionTransfer())->addCompanyBusinessUnit(
                (new CompanyBusinessUnitTransfer())->setIdCompanyBusinessUnit(static::ID_COMPANY_BUSINESS_UNIT_OWN),
            ));
        $businessUnitController = $this->createBusinessUnitControllerMock($companyBusinessUnitClientMock);

        // Act
        $companyBusinessUnitTransfer = $this->callProtectedMethod($businessUnitController, 'findCompanyBusinessUnitForCurrentCompany', [static::ID_COMPANY_BUSINESS_UNIT_OWN]);

        // Assert
        $this->assertSame(static::ID_COMPANY_BUSINESS_UNIT_OWN, $companyBusinessUnitTransfer->getIdCompanyBusinessUnit());
    }

    /**
     * @dataProvider provideActionNames
     */
    public function testActionThrowsNotFoundWithoutByIdLookupWhenBusinessUnitIsNotInCurrentCompany(string $actionName): void
    {
        // Arrange
        $companyBusinessUnitClientMock = $this->createCompanyBusinessUnitClientMock();
        $companyBusinessUnitClientMock->method('getCompanyBusinessUnitCollection')->willReturn(new CompanyBusinessUnitCollectionTransfer());
        $companyBusinessUnitClientMock->expects($this->never())->method('getCompanyBusinessUnitById');
        $companyBusinessUnitClientMock->expects($this->never())->method('deleteCompanyBusinessUnit');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createBusinessUnitControllerMock($companyBusinessUnitClientMock)->$actionName(new Request(['id' => static::ID_COMPANY_BUSINESS_UNIT_NOT_IN_COMPANY]));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideActionNames(): array
    {
        return [
            'details' => ['detailsAction'],
            'update' => ['updateAction'],
            'delete' => ['deleteAction'],
            'confirm delete' => ['confirmDeleteAction'],
        ];
    }

    /**
     * @param array<mixed> $arguments
     */
    protected function callProtectedMethod(BusinessUnitController $businessUnitController, string $methodName, array $arguments): mixed
    {
        return (new ReflectionMethod($businessUnitController, $methodName))->invokeArgs($businessUnitController, $arguments);
    }

    protected function createCompanyBusinessUnitClientMock(): CompanyPageToCompanyBusinessUnitClientBridge|MockObject
    {
        return $this->getMockBuilder(CompanyPageToCompanyBusinessUnitClientBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCompanyBusinessUnitCollection', 'getCompanyBusinessUnitById', 'deleteCompanyBusinessUnit'])
            ->getMock();
    }

    protected function createFormFactoryMock(): FormFactory|MockObject
    {
        $formMock = $this->createMock(FormInterface::class);
        $formMock->method('handleRequest')->willReturnSelf();
        $formMock->method('isSubmitted')->willReturn(false);

        $deleteFormMock = $this->createMock(FormInterface::class);
        $deleteFormMock->method('handleRequest')->willReturnSelf();
        $deleteFormMock->method('isSubmitted')->willReturn(true);
        $deleteFormMock->method('isValid')->willReturn(true);

        $formFactoryMock = $this->createMock(FormFactory::class);
        $formFactoryMock->method('createBusinessUnitFormDataProvider')->willReturn($this->createMock(CompanyBusinessUnitFormDataProvider::class));
        $formFactoryMock->method('getBusinessUnitForm')->willReturn($formMock);
        $formFactoryMock->method('getCompanyBusinessUnitDeleteForm')->willReturn($deleteFormMock);

        return $formFactoryMock;
    }

    protected function createBusinessUnitControllerMock(CompanyPageToCompanyBusinessUnitClientBridge $companyBusinessUnitClientMock): BusinessUnitController
    {
        $businessUnitControllerMock = $this->createPartialMock(BusinessUnitController::class, ['getFactory', 'findCurrentCompanyUserTransfer']);

        $factoryMock = $this->createPartialMock(CompanyPageFactory::class, ['getCompanyBusinessUnitClient', 'createCompanyPageFormFactory']);
        $factoryMock->method('getCompanyBusinessUnitClient')->willReturn($companyBusinessUnitClientMock);
        $factoryMock->method('createCompanyPageFormFactory')->willReturn($this->createFormFactoryMock());

        $businessUnitControllerMock->method('getFactory')->willReturn($factoryMock);
        $businessUnitControllerMock
            ->method('findCurrentCompanyUserTransfer')
            ->willReturn((new CompanyUserTransfer())->setFkCompany(static::ID_COMPANY_SESSION));

        return $businessUnitControllerMock;
    }
}
