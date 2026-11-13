<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CompanyPage\Controller;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerShop\Yves\CompanyPage\Controller\AddressController;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyUnitAddressClientInterface;
use SprykerShop\Yves\CompanyPage\Form\CompanyUnitAddressForm;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressSaverInterface;
use SprykerShopTest\Yves\CompanyPage\CompanyPageTester;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerShopTest
 * @group Yves
 * @group CompanyPage
 * @group Controller
 * @group AddressControllerTest
 * Add your own group annotations below this line
 */
class AddressControllerTest extends Unit
{
    protected CompanyPageTester $tester;

    public function testUpdateActionThrowsNotFoundWhenSubmittedAddressBelongsToAnotherCompany(): void
    {
        // Arrange
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->never())->method('saveAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createAddressController($saverMock, $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER))
            ->updateAction(new Request(['idCompanyBusinessUnit' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN]));
    }

    public function testUpdateActionThrowsNotFoundWhenRequestedAddressBelongsToAnotherCompanyOnUnsubmittedForm(): void
    {
        // Arrange
        $form = $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN, false);

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createAddressController($this->createSaverMock(), $form)->updateAction(new Request([
            'id' => CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER,
            'idCompanyBusinessUnit' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN,
        ]));
    }

    public function testUpdateActionSavesSubmittedAddressWithOwnerIdentifiersFromSession(): void
    {
        // Arrange
        $saverMock = $this->createSaverMockExpectingIdentifiers(
            CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN,
            CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN,
        );

        // Act
        $response = $this->createAddressController($saverMock, $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN))
            ->updateAction(new Request(['idCompanyBusinessUnit' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN]));

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCreateActionIgnoresSubmittedIdentifiers(): void
    {
        // Arrange
        $saverMock = $this->createSaverMockExpectingIdentifiers(null, null);

        // Act
        $response = $this->createAddressController($saverMock, $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER))
            ->createAction(new Request());

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCreateActionSavesAddressForBusinessUnitOfCurrentCompany(): void
    {
        // Arrange
        $saverMock = $this->createSaverMockExpectingIdentifiers(null, CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN);

        // Act
        $response = $this->createAddressController($saverMock, $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER))
            ->createAction(new Request(['idCompanyBusinessUnit' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN]));

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCreateActionThrowsNotFoundWhenCustomerHasNoCompanyUser(): void
    {
        // Arrange
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->never())->method('saveAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createAddressController($saverMock, $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER), false)
            ->createAction(new Request());
    }

    /**
     * @dataProvider provideActionNames
     */
    public function testActionThrowsNotFoundWhenBusinessUnitBelongsToAnotherCompany(string $actionName): void
    {
        // Arrange
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->never())->method('saveAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createAddressController($saverMock, $this->tester->createFormStub(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN))
            ->$actionName(new Request(['idCompanyBusinessUnit' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OTHER]));
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideActionNames(): array
    {
        return [
            'create' => ['createAction'],
            'update' => ['updateAction'],
        ];
    }

    public function testCreateActionWithRealFormReplacesSubmittedIdentifiersWithSessionValues(): void
    {
        // Arrange
        $saverMock = $this->createSaverMockExpectingIdentifiers(null, CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN);
        $form = $this->tester->createRealCompanyUnitAddressForm();

        // Act
        $response = $this->createAddressController($saverMock, $form)
            ->createAction($this->createPostRequest($form, CompanyPageTester::ID_COMPANY_SESSION));

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCreateActionWithRealFormDoesNotSaveWhenSubmittedCompanyIsNotSessionCompany(): void
    {
        // Arrange
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->never())->method('saveAddress');
        $form = $this->tester->createRealCompanyUnitAddressForm();

        // Act
        $this->createAddressController($saverMock, $form)
            ->createAction($this->createPostRequest($form, CompanyPageTester::ID_COMPANY_OTHER));

        // Assert
        $this->assertFormHasOnlyErrorOfField($form, CompanyUnitAddressForm::FIELD_FK_COMPANY);
    }

    protected function assertFormHasOnlyErrorOfField(FormInterface $form, string $fieldName): void
    {
        $formErrors = $form->getErrors(true);
        $formError = $formErrors->current();

        $this->assertCount(1, $formErrors);
        $this->assertInstanceOf(FormError::class, $formError);
        $this->assertSame($fieldName, $formError->getOrigin()?->getName());
    }

    protected function createAddressController(
        CompanyBusinessUnitAddressSaverInterface $saver,
        FormInterface $form,
        bool $hasCurrentCompanyUser = true
    ): AddressController {
        $companyUnitAddressClientMock = $this->tester->configureCompanyUnitAddressClientMock(
            $this->createMock(CompanyPageToCompanyUnitAddressClientInterface::class),
        );
        $companyPageFactory = $this->tester->createCompanyPageFactoryStub($this->tester->createFormFactoryStub($form), $companyUnitAddressClientMock, $saver);

        return $this->tester->createCompanyControllerStub(AddressController::class, $companyPageFactory, $hasCurrentCompanyUser);
    }

    protected function createSaverMock(): CompanyBusinessUnitAddressSaverInterface&MockObject
    {
        return $this->createMock(CompanyBusinessUnitAddressSaverInterface::class);
    }

    protected function createSaverMockExpectingIdentifiers(
        ?int $idCompanyUnitAddress,
        ?int $idCompanyBusinessUnit
    ): CompanyBusinessUnitAddressSaverInterface&MockObject {
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->once())
            ->method('saveAddress')
            ->with($this->callback(function (array $data) use ($idCompanyUnitAddress, $idCompanyBusinessUnit): bool {
                return $data[CompanyUnitAddressForm::FIELD_ID_COMPANY_UNIT_ADDRESS] === $idCompanyUnitAddress
                    && $data[CompanyUnitAddressForm::FIELD_FK_COMPANY] === CompanyPageTester::ID_COMPANY_SESSION
                    && $data[CompanyUnitAddressForm::FIELD_FK_COMPANY_BUSINESS_UNIT] === $idCompanyBusinessUnit;
            }))
            ->willReturn(new CompanyUnitAddressTransfer());

        return $saverMock;
    }

    protected function createPostRequest(FormInterface $form, int $idSubmittedCompany): Request
    {
        return Request::create(
            sprintf('/?idCompanyBusinessUnit=%d', CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN),
            Request::METHOD_POST,
            [$form->getName() => $this->tester->createValidCompanyUnitAddressFormData($idSubmittedCompany)],
        );
    }
}
