<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerShop\Yves\CompanyPage\Dependency\Client;

use Generated\Shared\Transfer\CompanyResponseTransfer;
use Generated\Shared\Transfer\CompanyTransfer;

interface CompanyPageToCompanyClientInterface
{
    public function createCompany(CompanyTransfer $companyTransfer): CompanyResponseTransfer;

    public function getCompanyById(CompanyTransfer $companyTransfer): CompanyTransfer;
}
