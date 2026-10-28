<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CompanyPage\Controller;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyUserCollectionTransfer;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserResponseTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionMethod;
use SprykerShop\Yves\CompanyPage\CompanyPageFactory;
use SprykerShop\Yves\CompanyPage\Controller\UserController;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyUserClientBridge;
use SprykerShop\Yves\CompanyPage\Form\CompanyUserForm;
use SprykerShop\Yves\CompanyPage\Form\DataProvider\CompanyUserFormDataProvider;
use SprykerShop\Yves\CompanyPage\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group SprykerShopTest
 * @group Yves
 * @group CompanyPage
 * @group Controller
 * @group UserControllerTest
 */
class UserControllerTest extends Unit
{
    protected const int ID_COMPANY_USER_FOREIGN = 21;

    protected const int ID_COMPANY_USER_OWN = 22;

    protected const int ID_CUSTOMER_FOREIGN = 51;

    protected const int ID_CUSTOMER_OWN = 52;

    protected const int ID_COMPANY_SESSION = 1;

    protected const int ID_COMPANY_FOREIGN = 2;

    /**
     * @var \SprykerShopTest\Yves\CompanyPage\CompanyPageTester
     */
    protected $tester;

    public function testUpdateActionThrowsNotFoundOnDisplayWhenCompanyUserIsNotInCurrentCompany(): void
    {
        // Arrange
        $companyUserClientMock = $this->createCompanyUserClientMock();
        $companyUserClientMock->expects($this->once())
            ->method('getCompanyUserCollection')
            ->with($this->callback(function (CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer): bool {
                return $companyUserCriteriaFilterTransfer->getIdCompany() === static::ID_COMPANY_SESSION
                    && $companyUserCriteriaFilterTransfer->getCompanyUserIds() === [static::ID_COMPANY_USER_FOREIGN];
            }))
            ->willReturn(new CompanyUserCollectionTransfer());
        $formFactoryMock = $this->createFormFactoryMock(false, []);

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createUserControllerMock($companyUserClientMock, $formFactoryMock)->updateAction(new Request(['id' => static::ID_COMPANY_USER_FOREIGN]));
    }

    public function testUpdateActionThrowsNotFoundOnSubmitWhenCompanyUserIsNotInCurrentCompany(): void
    {
        // Arrange
        $companyUserClientMock = $this->createCompanyUserClientMock();
        $companyUserClientMock->expects($this->once())
            ->method('getCompanyUserCollection')
            ->with($this->callback(function (CompanyUserCriteriaFilterTransfer $companyUserCriteriaFilterTransfer): bool {
                return $companyUserCriteriaFilterTransfer->getIdCompany() === static::ID_COMPANY_SESSION
                    && $companyUserCriteriaFilterTransfer->getCompanyUserIds() === [static::ID_COMPANY_USER_FOREIGN];
            }))
            ->willReturn(new CompanyUserCollectionTransfer());
        $companyUserClientMock->expects($this->never())->method('updateCompanyUser');
        $formFactoryMock = $this->createFormFactoryMock(true, $this->createTamperedFormData());

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createUserControllerMock($companyUserClientMock, $formFactoryMock)->updateAction(new Request(['id' => static::ID_COMPANY_USER_FOREIGN]));
    }

    /**
     * @dataProvider provideDeleteActionNames
     */
    public function testDeleteActionThrowsNotFoundWithoutByIdLookupWhenCompanyUserIsNotInCurrentCompany(string $actionName): void
    {
        // Arrange
        $companyUserClientMock = $this->createCompanyUserClientMock();
        $companyUserClientMock->method('getCompanyUserCollection')->willReturn(new CompanyUserCollectionTransfer());
        $companyUserClientMock->expects($this->never())->method('getCompanyUserById');
        $companyUserClientMock->expects($this->never())->method('deleteCompanyUser');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createUserControllerMock($companyUserClientMock, $this->createDeleteFormFactoryMock())->$actionName(new Request(['id' => static::ID_COMPANY_USER_FOREIGN]));
    }

    public function testDeleteActionDeletesCompanyUserFromCurrentCompanyLookupWithoutByIdLookup(): void
    {
        // Arrange
        $companyUserTransfer = (new CompanyUserTransfer())->setIdCompanyUser(static::ID_COMPANY_USER_OWN)->setFkCompany(static::ID_COMPANY_SESSION);
        $companyUserClientMock = $this->createCompanyUserClientMock();
        $companyUserClientMock->method('getCompanyUserCollection')->willReturn((new CompanyUserCollectionTransfer())->addCompanyUser($companyUserTransfer));
        $companyUserClientMock->expects($this->never())->method('getCompanyUserById');
        $companyUserClientMock->expects($this->once())->method('deleteCompanyUser')->with($companyUserTransfer)->willReturn(new CompanyUserResponseTransfer());

        // Act
        $this->createUserControllerMockWithRedirect($companyUserClientMock, $this->createDeleteFormFactoryMock(static::ID_COMPANY_USER_OWN))->deleteAction(new Request());
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideDeleteActionNames(): array
    {
        return [
            'delete' => ['deleteAction'],
            'confirm delete' => ['confirmDeleteAction'],
        ];
    }

    public function testCreateCompanyUserDiscardsSubmittedIdentityAndUsesSessionCompany(): void
    {
        // Arrange
        $companyUserClientMock = $this->createCompanyUserClientMock();
        $companyUserClientMock->expects($this->once())
            ->method('createCompanyUser')
            ->with($this->callback(function (CompanyUserTransfer $companyUserTransfer): bool {
                return $companyUserTransfer->getIdCompanyUser() === null
                    && $companyUserTransfer->getFkCustomer() === null
                    && $companyUserTransfer->getFkCompany() === static::ID_COMPANY_SESSION
                    && $companyUserTransfer->getCustomer()->getIdCustomer() === null;
            }))
            ->willReturn(new CompanyUserResponseTransfer());

        // Act
        $this->callProtectedMethod($this->createUserControllerMock($companyUserClientMock), 'createCompanyUser', [$this->createTamperedFormData()]);
    }

    public function testUpdateCompanyUserTakesIdentityFromPersistedCompanyUser(): void
    {
        // Arrange
        $persistedCompanyUserTransfer = (new CompanyUserTransfer())
            ->setIdCompanyUser(static::ID_COMPANY_USER_OWN)
            ->setFkCompany(static::ID_COMPANY_SESSION)
            ->setFkCustomer(static::ID_CUSTOMER_OWN);
        $companyUserClientMock = $this->createCompanyUserClientMock();
        $companyUserClientMock->expects($this->once())
            ->method('updateCompanyUser')
            ->with($this->callback(function (CompanyUserTransfer $companyUserTransfer): bool {
                return $companyUserTransfer->getIdCompanyUser() === static::ID_COMPANY_USER_OWN
                    && $companyUserTransfer->getFkCompany() === static::ID_COMPANY_SESSION
                    && $companyUserTransfer->getFkCustomer() === static::ID_CUSTOMER_OWN
                    && $companyUserTransfer->getCustomer()->getIdCustomer() === static::ID_CUSTOMER_OWN;
            }))
            ->willReturn(new CompanyUserResponseTransfer());

        // Act
        $this->callProtectedMethod($this->createUserControllerMock($companyUserClientMock), 'updateCompanyUser', [$this->createTamperedFormData(), $persistedCompanyUserTransfer]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function createTamperedFormData(): array
    {
        return [
            CompanyUserForm::FIELD_ID_COMPANY_USER => static::ID_COMPANY_USER_FOREIGN,
            CompanyUserForm::FIELD_ID_CUSTOMER => static::ID_CUSTOMER_FOREIGN,
            CompanyUserForm::FIELD_FK_CUSTOMER => static::ID_CUSTOMER_FOREIGN,
            CompanyUserForm::FIELD_FK_COMPANY => static::ID_COMPANY_FOREIGN,
        ];
    }

    /**
     * @param array<mixed> $arguments
     */
    protected function callProtectedMethod(UserController $userController, string $methodName, array $arguments): mixed
    {
        return (new ReflectionMethod($userController, $methodName))->invokeArgs($userController, $arguments);
    }

    protected function createCompanyUserClientMock(): CompanyPageToCompanyUserClientBridge|MockObject
    {
        return $this->getMockBuilder(CompanyPageToCompanyUserClientBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCompanyUserCollection', 'getCompanyUserById', 'createCompanyUser', 'updateCompanyUser', 'deleteCompanyUser'])
            ->getMock();
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function createFormFactoryMock(bool $isSubmitted, array $data): FormFactory|MockObject
    {
        $dataProviderMock = $this->createMock(CompanyUserFormDataProvider::class);
        $dataProviderMock->method('getOptions')->willReturn([]);
        $dataProviderMock->method('getData')->willReturn($data);

        $formMock = $this->createMock(FormInterface::class);
        $formMock->method('handleRequest')->willReturnSelf();
        $formMock->method('isSubmitted')->willReturn($isSubmitted);
        $formMock->method('isValid')->willReturn(true);
        $formMock->method('getData')->willReturn($data);

        $formFactoryMock = $this->createMock(FormFactory::class);
        $formFactoryMock->method('createCompanyUserFormDataProvider')->willReturn($dataProviderMock);
        $formFactoryMock->method('getCompanyUserForm')->willReturn($formMock);

        return $formFactoryMock;
    }

    protected function createDeleteFormFactoryMock(int $idCompanyUser = self::ID_COMPANY_USER_FOREIGN): FormFactory|MockObject
    {
        $deleteFormMock = $this->createMock(FormInterface::class);
        $deleteFormMock->method('handleRequest')->willReturnSelf();
        $deleteFormMock->method('isSubmitted')->willReturn(true);
        $deleteFormMock->method('isValid')->willReturn(true);
        $deleteFormMock->method('getData')->willReturn((new CompanyUserTransfer())->setIdCompanyUser($idCompanyUser));

        $formFactoryMock = $this->createMock(FormFactory::class);
        $formFactoryMock->method('getCompanyUserDeleteForm')->willReturn($deleteFormMock);

        return $formFactoryMock;
    }

    protected function createUserControllerMock(
        CompanyPageToCompanyUserClientBridge $companyUserClientMock,
        ?FormFactory $formFactoryMock = null
    ): UserController {
        $userControllerMock = $this->createPartialMock(
            UserController::class,
            ['can', 'getFactory', 'findCurrentCompanyUserTransfer'],
        );

        $factoryMock = $this->createPartialMock(CompanyPageFactory::class, ['getCompanyUserClient', 'createCompanyPageFormFactory']);
        $factoryMock->method('getCompanyUserClient')->willReturn($companyUserClientMock);

        if ($formFactoryMock !== null) {
            $factoryMock->method('createCompanyPageFormFactory')->willReturn($formFactoryMock);
        }

        $userControllerMock->method('can')->willReturn(true);
        $userControllerMock->method('getFactory')->willReturn($factoryMock);
        $userControllerMock
            ->method('findCurrentCompanyUserTransfer')
            ->willReturn((new CompanyUserTransfer())->setFkCompany(static::ID_COMPANY_SESSION));

        return $userControllerMock;
    }

    protected function createUserControllerMockWithRedirect(
        CompanyPageToCompanyUserClientBridge $companyUserClientMock,
        FormFactory $formFactoryMock
    ): UserController {
        $userControllerMock = $this->createPartialMock(
            UserController::class,
            ['can', 'getFactory', 'findCurrentCompanyUserTransfer', 'addErrorMessage', 'redirectResponseInternal'],
        );

        $factoryMock = $this->createPartialMock(CompanyPageFactory::class, ['getCompanyUserClient', 'createCompanyPageFormFactory']);
        $factoryMock->method('getCompanyUserClient')->willReturn($companyUserClientMock);
        $factoryMock->method('createCompanyPageFormFactory')->willReturn($formFactoryMock);

        $userControllerMock->method('can')->willReturn(true);
        $userControllerMock->method('getFactory')->willReturn($factoryMock);
        $userControllerMock->method('redirectResponseInternal')->willReturn(new RedirectResponse('/'));
        $userControllerMock
            ->method('findCurrentCompanyUserTransfer')
            ->willReturn((new CompanyUserTransfer())->setFkCompany(static::ID_COMPANY_SESSION));

        return $userControllerMock;
    }
}
