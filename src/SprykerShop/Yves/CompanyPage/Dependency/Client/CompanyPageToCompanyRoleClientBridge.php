<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Dependency\Client;

use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyRolePermissionResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\PermissionCollectionTransfer;
use Generated\Shared\Transfer\PermissionTransfer;

class CompanyPageToCompanyRoleClientBridge implements CompanyPageToCompanyRoleClientInterface
{
    /**
     * @var \Spryker\Client\CompanyRole\CompanyRoleClientInterface
     */
    protected $companyRoleClient;

    /**
     * @param \Spryker\Client\CompanyRole\CompanyRoleClientInterface $companyRoleClient
     */
    public function __construct($companyRoleClient)
    {
        $this->companyRoleClient = $companyRoleClient;
    }

    public function createCompanyRole(CompanyRoleTransfer $companyRoleUserTransfer): CompanyRoleResponseTransfer
    {
        return $this->companyRoleClient->createCompanyRole($companyRoleUserTransfer);
    }

    public function getCompanyRoleCollection(
        CompanyRoleCriteriaFilterTransfer $criteriaFilterTransfer
    ): CompanyRoleCollectionTransfer {
        return $this->companyRoleClient->getCompanyRoleCollection($criteriaFilterTransfer);
    }

    public function getCompanyRoleById(CompanyRoleTransfer $companyRoleTransfer): CompanyRoleTransfer
    {
        return $this->companyRoleClient->getCompanyRoleById($companyRoleTransfer);
    }

    public function updateCompanyRole(CompanyRoleTransfer $companyRoleTransfer): void
    {
        $this->companyRoleClient->updateCompanyRole($companyRoleTransfer);
    }

    public function deleteCompanyRole(CompanyRoleTransfer $companyRoleTransfer): CompanyRoleResponseTransfer
    {
        return $this->companyRoleClient->deleteCompanyRole($companyRoleTransfer);
    }

    public function findCompanyRolePermissions(CompanyRoleTransfer $companyRoleTransfer): PermissionCollectionTransfer
    {
        return $this->companyRoleClient->findCompanyRolePermissions($companyRoleTransfer);
    }

    public function findPermissionByIdCompanyRoleByIdPermission(PermissionTransfer $permissionTransfer): PermissionTransfer
    {
        return $this->companyRoleClient->findPermissionByIdCompanyRoleByIdPermission($permissionTransfer);
    }

    public function updateCompanyRolePermission(PermissionTransfer $permissionTransfer): CompanyRolePermissionResponseTransfer
    {
        return $this->companyRoleClient->updateCompanyRolePermission($permissionTransfer);
    }

    public function saveCompanyUser(CompanyUserTransfer $companyUserTransfer): void
    {
        $this->companyRoleClient->saveCompanyUser($companyUserTransfer);
    }

    public function findNonInfrastructuralCompanyRolePermissionsByIdCompanyRole(
        CompanyRoleTransfer $companyRoleTransfer
    ): PermissionCollectionTransfer {
        return $this->companyRoleClient->findNonInfrastructuralCompanyRolePermissionsByIdCompanyRole($companyRoleTransfer);
    }
}
