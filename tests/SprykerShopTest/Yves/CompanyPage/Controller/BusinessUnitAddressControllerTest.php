<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShopTest\Yves\CompanyPage\Controller;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyUnitAddressResponseTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use SprykerShop\Yves\CompanyPage\Controller\BusinessUnitAddressController;
use SprykerShop\Yves\CompanyPage\Dependency\Client\CompanyPageToCompanyUnitAddressClientInterface;
use SprykerShop\Yves\CompanyPage\Form\CompanyBusinessUnitAddressForm;
use SprykerShop\Yves\CompanyPage\Model\CompanyBusinessUnit\CompanyBusinessUnitAddressSaverInterface;
use SprykerShopTest\Yves\CompanyPage\CompanyPageTester;
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
 * @group BusinessUnitAddressControllerTest
 * Add your own group annotations below this line
 */
class BusinessUnitAddressControllerTest extends Unit
{
    protected CompanyPageTester $tester;

    public function testUpdateActionThrowsNotFoundWhenRequestedAddressBelongsToAnotherCompany(): void
    {
        // Arrange
        $companyUnitAddressClientMock = $this->createCompanyUnitAddressClientMock();
        $companyUnitAddressClientMock->expects($this->never())->method('updateCompanyUnitAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createBusinessUnitAddressController($companyUnitAddressClientMock, $this->createSaverMock())
            ->updateAction($this->createUpdateRequest(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER, CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN));
    }

    public function testUpdateActionThrowsNotFoundWhenBusinessUnitBelongsToAnotherCompany(): void
    {
        // Arrange
        $companyUnitAddressClientMock = $this->createCompanyUnitAddressClientMock();
        $companyUnitAddressClientMock->expects($this->never())->method('updateCompanyUnitAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createBusinessUnitAddressController($companyUnitAddressClientMock, $this->createSaverMock())
            ->updateAction($this->createUpdateRequest(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN, CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OTHER));
    }

    public function testUpdateActionThrowsNotFoundWhenSubmittedAddressBelongsToAnotherCompany(): void
    {
        // Arrange
        $companyUnitAddressClientMock = $this->createCompanyUnitAddressClientMock();
        $companyUnitAddressClientMock->expects($this->never())->method('updateCompanyUnitAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createBusinessUnitAddressController($companyUnitAddressClientMock, $this->createSaverMock(), CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER)
            ->updateAction($this->createUpdateRequest(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN, CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN));
    }

    public function testUpdateActionSavesSubmittedAddressWithOwnerIdentifiersFromSession(): void
    {
        // Arrange
        $companyUnitAddressClientMock = $this->createCompanyUnitAddressClientMockExpectingUpdate();

        // Act
        $response = $this->createBusinessUnitAddressController($companyUnitAddressClientMock, $this->createSaverMock())
            ->updateAction($this->createUpdateRequest(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN, CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN));

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    public function testCreateActionThrowsNotFoundWhenBusinessUnitBelongsToAnotherCompany(): void
    {
        // Arrange
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->never())->method('saveAddress');

        // Expect
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->createBusinessUnitAddressController($this->createCompanyUnitAddressClientMock(), $saverMock)
            ->createAction(new Request(['id' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OTHER]));
    }

    public function testCreateActionIgnoresSubmittedIdentifiers(): void
    {
        // Arrange
        $saverMock = $this->createSaverMockExpectingSessionIdentifiers();

        // Act
        $response = $this->createBusinessUnitAddressController($this->createCompanyUnitAddressClientMock(), $saverMock, CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OTHER)
            ->createAction(new Request(['id' => CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN]));

        // Assert
        $this->assertInstanceOf(RedirectResponse::class, $response);
    }

    protected function createUpdateRequest(int $idCompanyUnitAddress, int $idCompanyBusinessUnit): Request
    {
        return new Request([
            'id' => $idCompanyUnitAddress,
            'idCompanyBusinessUnit' => $idCompanyBusinessUnit,
        ]);
    }

    protected function createBusinessUnitAddressController(
        CompanyPageToCompanyUnitAddressClientInterface $companyUnitAddressClient,
        CompanyBusinessUnitAddressSaverInterface $saver,
        int $idSubmittedCompanyUnitAddress = CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN
    ): BusinessUnitAddressController {
        $formFactory = $this->tester->createFormFactoryStub($this->tester->createFormStub($idSubmittedCompanyUnitAddress));
        $companyPageFactory = $this->tester->createCompanyPageFactoryStub($formFactory, $companyUnitAddressClient, $saver);

        return $this->tester->createCompanyControllerStub(BusinessUnitAddressController::class, $companyPageFactory);
    }

    protected function createCompanyUnitAddressClientMock(): CompanyPageToCompanyUnitAddressClientInterface&MockObject
    {
        return $this->tester->configureCompanyUnitAddressClientMock($this->createMock(CompanyPageToCompanyUnitAddressClientInterface::class));
    }

    protected function createCompanyUnitAddressClientMockExpectingUpdate(): CompanyPageToCompanyUnitAddressClientInterface&MockObject
    {
        $companyUnitAddressClientMock = $this->createCompanyUnitAddressClientMock();
        $companyUnitAddressClientMock->expects($this->once())
            ->method('updateCompanyUnitAddress')
            ->with($this->callback(function (CompanyUnitAddressTransfer $companyUnitAddressTransfer): bool {
                return $companyUnitAddressTransfer->getIdCompanyUnitAddress() === CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN
                    && $companyUnitAddressTransfer->getFkCompany() === CompanyPageTester::ID_COMPANY_SESSION
                    && $companyUnitAddressTransfer->getFkCompanyBusinessUnit() === CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN;
            }))
            ->willReturn((new CompanyUnitAddressResponseTransfer())
                ->setIsSuccessful(true)
                ->setCompanyUnitAddressTransfer(new CompanyUnitAddressTransfer()));

        return $companyUnitAddressClientMock;
    }

    protected function createSaverMock(): CompanyBusinessUnitAddressSaverInterface&MockObject
    {
        return $this->createMock(CompanyBusinessUnitAddressSaverInterface::class);
    }

    protected function createSaverMockExpectingSessionIdentifiers(): CompanyBusinessUnitAddressSaverInterface&MockObject
    {
        $saverMock = $this->createSaverMock();
        $saverMock->expects($this->once())
            ->method('saveAddress')
            ->with($this->callback(function (array $data): bool {
                return $data[CompanyBusinessUnitAddressForm::FIELD_ID_COMPANY_UNIT_ADDRESS] === null
                    && $data[CompanyBusinessUnitAddressForm::FIELD_FK_COMPANY] === CompanyPageTester::ID_COMPANY_SESSION
                    && $data[CompanyBusinessUnitAddressForm::FIELD_FK_COMPANY_BUSINESS_UNIT] === CompanyPageTester::ID_COMPANY_BUSINESS_UNIT_OWN;
            }))
            ->willReturn((new CompanyUnitAddressTransfer())->setIdCompanyUnitAddress(CompanyPageTester::ID_COMPANY_UNIT_ADDRESS_OWN));

        return $saverMock;
    }
}
