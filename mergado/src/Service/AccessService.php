<?php declare(strict_types=1);

/**
 * NOTICE OF LICENSE.
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    www.mergado.cz
 *  @copyright 2016 Mergado technologies, s. r. o.
 *  @license   license.txt
 */


namespace Mergado\Service;

use Context;
use Profile;

class AccessService extends AbstractBaseService
{
    public function employeeHasAccessToModify(): bool
    {
        $context = Context::getContext();

        $profileId = $context->employee->id_profile;

        $accesses = Profile::getProfileAccesses($profileId);

        // Check if user is super admin
        if ($context->employee->isSuperAdmin()) {
            return true;
        }

        // Check if user profile has access to any off edit/add/delete for plugin
        foreach ($accesses as $access) {
            if ($access['module'] === 'mergado') {
                if ($access['edit'] === "1" || $access['add'] === '1' || $access['delete'] === "1") {
                    return true;
                }
            }
        }

        return false;
    }
}
