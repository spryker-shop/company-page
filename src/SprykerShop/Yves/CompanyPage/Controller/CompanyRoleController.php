<?php

namespace SprykerShop\Yves\CompanyPage\Controller;

use Generated\Shared\Transfer\CompanyRoleCollectionTransfer;
use Generated\Shared\Transfer\CompanyRoleResponseTransfer;
use Generated\Shared\Transfer\CompanyRoleTransfer;
use SprykerShop\Yves\CompanyPage\Plugin\Provider\CompanyPageControllerProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class CompanyRoleController extends AbstractCompanyController
{
    public const COMPANY_ROLE_SORT_FIELD = 'id_company_role';

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function indexAction(Request $request)
    {
        $data = $this->getCompanyRoleResponseData($request);

        return $this->view($data);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Spryker\Yves\Kernel\View\View
     */
    public function detailsAction(Request $request)
    {
        $idCompanyRole = $request->query->getInt('id');
        $companyRoleTransfer = new CompanyRoleTransfer();
        $companyRoleTransfer->setIdCompanyRole($idCompanyRole);
        $companyRoleTransfer = $this->getFactory()
            ->getCompanyRoleClient()
            ->getCompanyRoleById($companyRoleTransfer);

        $companyRolePermissions = $this->getFactory()
            ->getCompanyRoleClient()
            ->findCompanyRolePermissions($companyRoleTransfer);

        return $this->view([
            'companyRole' => $companyRoleTransfer,
            'permissions' => $companyRolePermissions->getPermissions(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request): RedirectResponse
    {
        $idCompanyRole = $request->query->getInt('id');
        $companyRoleTransfer = new CompanyRoleTransfer();
        $companyRoleTransfer->setIdCompanyRole($idCompanyRole);

        $this->getFactory()->getCompanyRoleClient()->deleteCompanyRole($companyRoleTransfer);

        return $this->redirectResponseInternal(CompanyPageControllerProvider::ROUTE_COMPANY_ROLE);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $dataProvider = $this->getFactory()
            ->createCompanyFormFactory()
            ->createCompanyRoleDataProvider();

        $companyRoleForm = $this
            ->getFactory()
            ->createCompanyFormFactory()
            ->getCompanyRoleForm()
            ->handleRequest($request);

        if ($companyRoleForm->isSubmitted() === false) {
            $idCompany = $this->getCompanyUser()->getFkCompany();
            $companyRoleForm->setData($dataProvider->getData($idCompany));
        }

        if ($companyRoleForm->isSubmitted() && $companyRoleForm->isValid()) {
            $companyRoleResponseTransfer = $this->createCompanyRole($companyRoleForm->getData());
            if ($companyRoleResponseTransfer->getIsSuccessful()) {
                return $this->redirectResponseInternal(CompanyPageControllerProvider::ROUTE_COMPANY_ROLE);
            }

            $this->processResponseMessages($companyRoleResponseTransfer);
        }

        return $this->view([
            'companyRoleForm' => $companyRoleForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array|\Spryker\Yves\Kernel\View\View|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateAction(Request $request)
    {
        $dataProvider = $this->getFactory()
            ->createCompanyFormFactory()
            ->createCompanyRoleDataProvider();

        $companyRoleForm = $this
            ->getFactory()
            ->createCompanyFormFactory()
            ->getCompanyRoleForm()
            ->handleRequest($request);

        if ($companyRoleForm->isSubmitted() === false) {
            $idCompanyRole = $request->query->getInt('id');
            $idCompany = $this->getCompanyUser()->getFkCompany();
            $companyRoleForm->setData($dataProvider->getData($idCompany, $idCompanyRole));
        }

        if ($companyRoleForm->isSubmitted() && $companyRoleForm->isValid()) {
            $this->updateCompanyRole($companyRoleForm->getData());

            return $this->redirectResponseInternal(CompanyPageControllerProvider::ROUTE_COMPANY_ROLE);
        }

        return $this->view([
            'companyRoleForm' => $companyRoleForm->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function getCompanyRoleResponseData(Request $request): array
    {
        $collectionTransfer = $this->createCompanyRoleCollectionTransfer($request);
        $collectionTransfer = $this->getFactory()
            ->getCompanyRoleClient()
            ->getCompanyRoleCollection($collectionTransfer);

        return [
            'companyRoleCollection' => $collectionTransfer->getRoles(),
            'pagination' => $collectionTransfer->getPagination(),
        ];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Generated\Shared\Transfer\CompanyRoleCollectionTransfer
     */
    protected function createCompanyRoleCollectionTransfer(Request $request): CompanyRoleCollectionTransfer
    {
        $collectionTransfer = new CompanyRoleCollectionTransfer();

        $idCompany = $this->getCompanyUser()->getFkCompany();
        $collectionTransfer->setIdCompany($idCompany);
        $collectionTransfer->setPagination($this->createPaginationTransfer($request));
        $collectionTransfer->setFilter($this->createFilterTransfer(static::COMPANY_ROLE_SORT_FIELD));

        return $collectionTransfer;
    }

    /**
     * @param array $data
     *
     * @return \Generated\Shared\Transfer\CompanyRoleResponseTransfer
     */
    protected function createCompanyRole(array $data): CompanyRoleResponseTransfer
    {
        $companyRoleTransfer = new CompanyRoleTransfer();
        $companyRoleTransfer->fromArray($data, true);

        return $this->getFactory()
            ->getCompanyRoleClient()
            ->createCompanyRole($companyRoleTransfer);
    }

    /**
     * @param array $data
     */
    protected function updateCompanyRole(array $data): void
    {
        $companyRoleTransfer = new CompanyRoleTransfer();
        $companyRoleTransfer->fromArray($data, true);

        $this->getFactory()
            ->getCompanyRoleClient()
            ->updateCompanyRole($companyRoleTransfer);
    }
}
