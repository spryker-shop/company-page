<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Controller;

use ArrayObject;
use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use Generated\Shared\Transfer\CompanyUserCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyUserTransfer;
use SprykerShop\Yves\CompanyPage\Plugin\Provider\CompanyPageControllerProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \SprykerShop\Yves\CompanyPage\CompanyPageFactory getFactory()
 */
class CompanyRoleUserController extends AbstractCompanyController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Spryker\Yves\Kernel\View\View
     */
    public function manageAction(Request $request)
    {
        $data = [
            'idCompanyRole' => $request->query->getInt('id'),
            'companyUserCollection' => $this->getCompanyUserList($request),
        ];

        return $this->view($data, [], '@CompanyPage/views/role-user/role-user.twig');
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function assignAction(Request $request): RedirectResponse
    {
        $idCompanyRole = $request->query->getInt('id-company-role');
        $idCompanyUser = $request->query->getInt('id-company-user');

        $companyRoles = $this->getFactory()
            ->getCompanyRoleClient()
            ->getCompanyRoleCollection(
                (new CompanyRoleCriteriaFilterTransfer())->setIdCompanyUser($idCompanyUser)
            )
            ->getRoles();

        $companyRoleTransfer = (new CompanyRoleTransfer())->setIdCompanyRole($idCompanyRole);
        $companyRoles->append($companyRoleTransfer);

        $this->saveCompanyUser($idCompanyUser, $companyRoles);

        return $this->redirectResponseInternal(
            CompanyPageControllerProvider::ROUTE_COMPANY_ROLE_USER_MANAGE,
            ['id' => $idCompanyRole]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unassignAction(Request $request): RedirectResponse
    {
        $idCompanyRole = $request->query->getInt('id-company-role');
        $idCompanyUser = $request->query->getInt('id-company-user');

        $companyRoles = new ArrayObject();
        $companyRoleList = $this->getFactory()
            ->getCompanyRoleClient()
            ->getCompanyRoleCollection(
                (new CompanyRoleCriteriaFilterTransfer())->setIdCompanyUser($idCompanyUser)
            )
            ->getRoles();

        foreach ($companyRoleList as $companyRoleTransfer) {
            if ($companyRoleTransfer->getIdCompanyRole() !== $idCompanyRole) {
                $companyRoles->append($companyRoleTransfer);
            }
        }

        $this->saveCompanyUser($idCompanyUser, $companyRoles);

        return $this->redirectResponseInternal(
            CompanyPageControllerProvider::ROUTE_COMPANY_ROLE_USER_MANAGE,
            ['id' => $idCompanyRole]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function getCompanyUserList(Request $request): array
    {
        $idCompanyRole = $request->query->getInt('id');

        $companyUserList = $this->getFactory()
            ->getCompanyUserClient()
            ->getCompanyUserCollection(
                (new CompanyUserCriteriaFilterTransfer())
                    ->setIdCompany($this->getCompanyUser()->getFkCompany())
            )
            ->getCompanyUsers();

        $companyRoleUserList = $this->getFactory()
            ->getCompanyRoleClient()
            ->getCompanyRoleById((new CompanyRoleTransfer())->setIdCompanyRole($idCompanyRole))
            ->getCompanyUserCollection()
            ->getCompanyUsers();

        $userList = [];

        foreach ($companyUserList as $companyUserTransfer) {
            $companyUserAsArray = $companyUserTransfer->toArray(true, true);
            $companyUserAsArray['idCompanyRole'] = null;

            foreach ($companyRoleUserList as $companyUserRoleTransfer) {
                if ($companyUserTransfer->getIdCompanyUser() === $companyUserRoleTransfer->getIdCompanyUser()) {
                    $companyUserAsArray['idCompanyRole'] = $idCompanyRole;
                }
            }

            $userList[] = $companyUserAsArray;
        }

        return $userList;
    }

    /**
     * @param int $idCompanyUser
     * @param \ArrayObject $companyRoles
     *
     * @return void
     */
    protected function saveCompanyUser(int $idCompanyUser, ArrayObject $companyRoles): void
    {
        $companyRoleCollection = (new CompanyRoleCollectionTransfer())
            ->setRoles($companyRoles);

        $companyUserTransfer = (new CompanyUserTransfer())
            ->setIdCompanyUser($idCompanyUser)
            ->setCompanyRoleCollection($companyRoleCollection);

        $this->getFactory()
            ->getCompanyRoleClient()
            ->saveCompanyUser($companyUserTransfer);
    }
}
