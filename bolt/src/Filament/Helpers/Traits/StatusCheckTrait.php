<?php

namespace LaraExperts\Bolt\Filament\Helpers\Traits;

use LaraExperts\Bolt\Filament\Helpers\APIResponse;
use LaraExperts\Bolt\Filament\Enums\Http;

trait StatusCheckTrait
{
    /**
     * Check if the user status is Active.
     *
     * @param object $user
     * @return mixed
     */
    public function checkUserStatus($user)
    {
        if ($user->status !== 'Active') {
            return new APIResponse(
                status: "fail",
                code: Http::FORBIDDEN,
                message: __('auth.account_disabled'),
                errors: ["user" => [__("auth.account_disabled")]]
            );
        }

        return true;
    }

    /**
     * Check if the company status is Active.
     *
     * @param object $company
     * @return mixed
     */
    public function checkCompanyStatus($company)
    {
        if ($company->status !== 'Active') {
            return new APIResponse(
                status: "fail",
                code: Http::FORBIDDEN,
                message: __('auth.company_disabled'),
                errors: ["company" => [__("auth.company_disabled")]]
            );
        }

        return true;
    }
    public  function check_user_company_activity($user,$company):mixed
    {
        $company= $user?->company;

        $companyStatus = $this->checkCompanyStatus($company);
        if ($companyStatus !== true) {
            return $companyStatus;
        }

        $userStatus = $this->checkUserStatus($user);
        if ($userStatus !== true) {
            return $userStatus;
        }
        return false;
    }

}
