<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CompanyPage\Controller;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use SprykerShop\Yves\CompanyPage\CompanyPageFactory;
use SprykerShop\Yves\CompanyPage\Controller\CompanyRoleController;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyRoleClientBridge;
use SprykerShop\Yves\CompanyPage\Form\DataProvider\CompanyRoleDataProvider;
use SprykerShop\Yves\CompanyPage\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CompanyPage
 * @group Controller
 * @group CompanyRoleControllerTest
 */
class CompanyRoleControllerTest extends Unit
{
    protected const int ID_COMPANY_SESSION = 1;

    protected const int ID_COMPANY_ROLE_OWN = 11;

    protected const int ID_COMPANY_ROLE_NOT_IN_COMPANY = 99;

    /**
     * @var \SprykerShopTest\Yves\CompanyPage\CompanyPageTester
     */
    protected $tester;

    public function testFindCompanyRoleForCurrentCompanyReturnsRoleOfSessionCompany(): void
    {
        // Arrange
        $companyRoleController = $this->createCompanyRoleControllerMock($this->createCompanyRoleClientMock(), $this->createFormFactoryMock());

        // Act
        $companyRoleTransfer = $this->callProtectedMethod($companyRoleController, 'findCompanyRoleForCurrentCompany', [static::ID_COMPANY_ROLE_OWN]);

        // Assert
        $this->assertSame(static::ID_COMPANY_ROLE_OWN, $companyRoleTransfer->getIdCompanyRole());
    }

    public function testFindCompanyRoleForCurrentCompanyReturnsNullWhenRoleIsNotInSessionCompany(): void
    {
        // Arrange
        $companyRoleController = $this->createCompanyRoleControllerMock($this->createCompanyRoleClientMock(), $this->createFormFactoryMock());

        // Act
        $companyRoleTransfer = $this->callProtectedMethod($companyRoleController, 'findCompanyRoleForCurrentCompany', [static::ID_COMPANY_ROLE_NOT_IN_COMPANY]);

        // Assert
        $this->assertNull($companyRoleTransfer);
    }

    /**
     * @dataProvider provideActionNames
     */
    public function testActionThrowsNotFoundWithoutByIdLookupWhenCompanyRoleIsNotInCurrentCompany(string $actionName): void
    {
        // Arrange
        $companyRoleClientMock = $this->createCompanyRoleClientMock();
        $companyRoleClientMock->expects($this->never())->method('getCompanyRoleById');
        $companyRoleClientMock->expects($this->never())->method('deleteCompanyRole');
        $companyRoleController = $this->createCompanyRoleControllerMock($companyRoleClientMock, $this->createFormFactoryMock());

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $companyRoleController->$actionName(new Request(['id' => static::ID_COMPANY_ROLE_NOT_IN_COMPANY]));
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
    protected function callProtectedMethod(CompanyRoleController $companyRoleController, string $methodName, array $arguments): mixed
    {
        return (new ReflectionMethod($companyRoleController, $methodName))->invokeArgs($companyRoleController, $arguments);
    }

    protected function createCompanyRoleClientMock(): CompanyPageToCompanyRoleClientBridge|MockObject
    {
        $companyRoleClientMock = $this->getMockBuilder(CompanyPageToCompanyRoleClientBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCompanyRoleCollection', 'getCompanyRoleById', 'deleteCompanyRole'])
            ->getMock();

        $companyRoleClientMock
            ->method('getCompanyRoleCollection')
            ->with($this->callback(function (CompanyRoleCriteriaFilterTransfer $companyRoleCriteriaFilterTransfer): bool {
                return $companyRoleCriteriaFilterTransfer->getIdCompany() === static::ID_COMPANY_SESSION;
            }))
            ->willReturn((new CompanyRoleCollectionTransfer())->addRole(
                (new CompanyRoleTransfer())->setIdCompanyRole(static::ID_COMPANY_ROLE_OWN)->setFkCompany(static::ID_COMPANY_SESSION),
            ));

        return $companyRoleClientMock;
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
        $deleteFormMock->method('getData')->willReturn((new CompanyRoleTransfer())->setIdCompanyRole(static::ID_COMPANY_ROLE_NOT_IN_COMPANY));

        $formFactoryMock = $this->createMock(FormFactory::class);
        $formFactoryMock->method('createCompanyRoleDataProvider')->willReturn($this->createMock(CompanyRoleDataProvider::class));
        $formFactoryMock->method('getCompanyRoleForm')->willReturn($formMock);
        $formFactoryMock->method('getCompanyRoleDeleteForm')->willReturn($deleteFormMock);

        return $formFactoryMock;
    }

    protected function createCompanyRoleControllerMock(
        CompanyPageToCompanyRoleClientBridge $companyRoleClientMock,
        FormFactory $formFactoryMock
    ): CompanyRoleController {
        $companyRoleControllerMock = $this->createPartialMock(
            CompanyRoleController::class,
            ['can', 'getFactory', 'findCurrentCompanyUserTransfer'],
        );

        $factoryMock = $this->createPartialMock(CompanyPageFactory::class, ['getCompanyRoleClient', 'createCompanyPageFormFactory']);
        $factoryMock->method('getCompanyRoleClient')->willReturn($companyRoleClientMock);
        $factoryMock->method('createCompanyPageFormFactory')->willReturn($formFactoryMock);

        $companyRoleControllerMock->method('can')->willReturn(true);
        $companyRoleControllerMock->method('getFactory')->willReturn($factoryMock);
        $companyRoleControllerMock
            ->method('findCurrentCompanyUserTransfer')
            ->willReturn((new CompanyUserTransfer())->setFkCompany(static::ID_COMPANY_SESSION));

        return $companyRoleControllerMock;
    }
}
