<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Controller;

use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyBusinessUnitTransfer;
use Generated\Shared\Transfer\CompanyRoleCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyTransfer;
use Generated\Shared\Transfer\CompanyUnitAddressTransfer;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use SprykerShop\Yves\CompanyPage\Form\CompanyUnitAddressForm;
use SprykerShop\Yves\ShopApplication\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method \SprykerShop\Yves\CompanyPage\CompanyPageFactory getFactory()
 */
abstract class AbstractCompanyController extends AbstractController
{
    /**
     * @var string
     */
    public const COMPANY_APPROVED_STATUS = 'approved';

    /**
     * @var string
     */
    public const PARAM_PAGE = 'page';

    /**
     * @var int
     */
    public const DEFAULT_PAGE = 1;

    protected function isCompanyActive(): bool
    {
        $companyUser = $this->findCurrentCompanyUserTransfer();

        if ($companyUser === null) {
            return false;
        }

        $companyTransfer = (new CompanyTransfer())->setIdCompany($companyUser->getFkCompany());
        $companyTransfer = $this->getFactory()
            ->getCompanyClient()
            ->getCompanyById($companyTransfer);

        return ($companyTransfer->getIsActive() === true && $companyTransfer->getStatus() === static::COMPANY_APPROVED_STATUS);
    }

    /**
     * @deprecated Use {@link \SprykerShop\Yves\CompanyPage\Controller\AbstractCompanyController::findCurrentCompanyUserTransfer()} instead.
     *
     * @return \Generated\Shared\Transfer\CompanyUserTransfer|null
     */
    protected function getCompanyUser(): ?CompanyUserTransfer
    {
        return $this->findCurrentCompanyUserTransfer();
    }

    protected function findCurrentCompanyUserTransfer(): ?CompanyUserTransfer
    {
        $customerTransfer = $this->getFactory()
            ->getCustomerClient()
            ->getCustomer();

        if (!$customerTransfer) {
            return null;
        }

        return $customerTransfer->getCompanyUserTransfer();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $limit
     *
     * @return \Generated\Shared\Transfer\PaginationTransfer
     */
    protected function createPaginationTransfer(Request $request, $limit = 10): PaginationTransfer
    {
        $paginationTransfer = new PaginationTransfer();
        $paginationTransfer->setPage($request->query->getInt(static::PARAM_PAGE, static::DEFAULT_PAGE));
        $paginationTransfer->setMaxPerPage($limit);

        return $paginationTransfer;
    }

    /**
     * @param string $field
     * @param string $direction
     *
     * @return \Generated\Shared\Transfer\FilterTransfer
     */
    protected function createFilterTransfer($field, $direction = 'DESC'): FilterTransfer
    {
        $filterTransfer = new FilterTransfer();
        $filterTransfer->setOrderBy($field);
        $filterTransfer->setOrderDirection($direction);

        return $filterTransfer;
    }

    protected function processResponseMessages(AbstractTransfer $responseTransfer): void
    {
        if ($responseTransfer->offsetExists('messages')) {
            /** @var array<\Generated\Shared\Transfer\ResponseMessageTransfer> $responseMessages */
            $responseMessages = $responseTransfer->offsetGet('messages');

            foreach ($responseMessages as $responseMessage) {
                $this->addErrorMessage($responseMessage->getText());
            }
        }
    }

    /**
     * @param string $key
     * @param string $locale
     * @param array<string, mixed> $params
     *
     * @return string
     */
    protected function getTranslatedMessage(string $key, string $locale, array $params = []): string
    {
        return $this->getFactory()
            ->getGlossaryStorageClient()
            ->translate($key, $locale, $params);
    }

    /**
     * @param string $key
     * @param array<string, mixed> $params
     *
     * @return void
     */
    protected function addTranslatedSuccessMessage(string $key, array $params = []): void
    {
        $message = $this->getTranslatedMessage($key, $this->getLocale(), $params);

        $this->addSuccessMessage($message);
    }

    /**
     * @param string $key
     * @param array<string, mixed> $params
     *
     * @return void
     */
    protected function addTranslatedErrorMessage(string $key, array $params = []): void
    {
        $message = $this->getTranslatedMessage($key, $this->getLocale(), $params);

        $this->addErrorMessage($message);
    }

    protected function isCurrentCustomerRelatedToCompany(int $idCompany): bool
    {
        $companyUserTransfer = $this->findCurrentCompanyUserTransfer();

        return ($companyUserTransfer !== null && $companyUserTransfer->getFkCompany() === $idCompany);
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function assertCompanyUnitAddressRelatedToCurrentCompany(int $idCompanyUnitAddress): CompanyUnitAddressTransfer
    {
        if (!$idCompanyUnitAddress) {
            throw new NotFoundHttpException();
        }

        $companyUnitAddressTransfer = $this->getFactory()
            ->getCompanyUnitAddressClient()
            ->getCompanyUnitAddressById((new CompanyUnitAddressTransfer())->setIdCompanyUnitAddress($idCompanyUnitAddress));
        $idCompany = $companyUnitAddressTransfer->getFkCompany();

        if ($idCompany === null || !$this->isCurrentCustomerRelatedToCompany($idCompany)) {
            throw new NotFoundHttpException();
        }

        return $companyUnitAddressTransfer;
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function assertCompanyBusinessUnitRelatedToCurrentCompany(int $idCompanyBusinessUnit): void
    {
        if ($idCompanyBusinessUnit && !$this->findCompanyBusinessUnitForCurrentCompany($idCompanyBusinessUnit)) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getCurrentCompanyIdOrFail(): int
    {
        $idCompany = $this->findCurrentCompanyUserTransfer()?->getFkCompany();

        if ($idCompany === null) {
            throw new NotFoundHttpException();
        }

        return $idCompany;
    }

    /**
     * @param array<string, mixed> $companyUnitAddressData
     *
     * @return array<string, mixed>
     */
    protected function mapCompanyUnitAddressIdentifiers(
        array $companyUnitAddressData,
        ?int $idCompanyUnitAddress,
        int $idCompanyBusinessUnit,
        int $idCompany
    ): array {
        $companyUnitAddressData[CompanyUnitAddressForm::FIELD_ID_COMPANY_UNIT_ADDRESS] = $idCompanyUnitAddress;
        $companyUnitAddressData[CompanyUnitAddressForm::FIELD_FK_COMPANY] = $idCompany;
        $companyUnitAddressData[CompanyUnitAddressForm::FIELD_FK_COMPANY_BUSINESS_UNIT] = $idCompanyBusinessUnit ?: null;

        return $companyUnitAddressData;
    }

    protected function getSelectablePermissionsList(int $idCompanyRole): PermissionCollectionTransfer
    {
        $companyRoleTransfer = (new CompanyRoleTransfer())
            ->setIdCompanyRole($idCompanyRole);

        return $this->getFactory()
            ->getCompanyRoleClient()
            ->findNonInfrastructuralCompanyRolePermissionsByIdCompanyRole($companyRoleTransfer);
    }

    protected function findCompanyRoleForCurrentCompany(int $idCompanyRole): ?CompanyRoleTransfer
    {
        $currentCompanyUserTransfer = $this->findCurrentCompanyUserTransfer();

        if ($idCompanyRole < 1 || $currentCompanyUserTransfer === null || !$currentCompanyUserTransfer->getFkCompany()) {
            return null;
        }

        $companyRoleTransfers = $this->getFactory()
            ->getCompanyRoleClient()
            ->getCompanyRoleCollection((new CompanyRoleCriteriaFilterTransfer())->setIdCompany($currentCompanyUserTransfer->getFkCompany()))
            ->getRoles();

        foreach ($companyRoleTransfers as $companyRoleTransfer) {
            if ($companyRoleTransfer->getIdCompanyRole() === $idCompanyRole) {
                return $companyRoleTransfer;
            }
        }

        return null;
    }

    protected function findCompanyUserForCurrentCompany(int $idCompanyUser): ?CompanyUserTransfer
    {
        $currentCompanyUserTransfer = $this->findCurrentCompanyUserTransfer();

        if ($idCompanyUser < 1 || $currentCompanyUserTransfer === null || !$currentCompanyUserTransfer->getFkCompany()) {
            return null;
        }

        $companyUserCriteriaFilterTransfer = (new CompanyUserCriteriaFilterTransfer())
            ->setIdCompany($currentCompanyUserTransfer->getFkCompany())
            ->setCompanyUserIds([$idCompanyUser]);

        $companyUserTransfers = $this->getFactory()
            ->getCompanyUserClient()
            ->getCompanyUserCollection($companyUserCriteriaFilterTransfer)
            ->getCompanyUsers();

        if ($companyUserTransfers->count() === 0) {
            return null;
        }

        return $companyUserTransfers->getIterator()->current();
    }

    protected function findCompanyBusinessUnitForCurrentCompany(int $idCompanyBusinessUnit): ?CompanyBusinessUnitTransfer
    {
        $currentCompanyUserTransfer = $this->findCurrentCompanyUserTransfer();

        if ($idCompanyBusinessUnit < 1 || $currentCompanyUserTransfer === null || !$currentCompanyUserTransfer->getFkCompany()) {
            return null;
        }

        $companyBusinessUnitCriteriaFilterTransfer = (new CompanyBusinessUnitCriteriaFilterTransfer())
            ->setIdCompany($currentCompanyUserTransfer->getFkCompany())
            ->setCompanyBusinessUnitIds([$idCompanyBusinessUnit]);

        $companyBusinessUnitTransfers = $this->getFactory()
            ->getCompanyBusinessUnitClient()
            ->getCompanyBusinessUnitCollection($companyBusinessUnitCriteriaFilterTransfer)
            ->getCompanyBusinessUnits();

        if ($companyBusinessUnitTransfers->count() === 0) {
            return null;
        }

        return $companyBusinessUnitTransfers->getIterator()->current();
    }
}
