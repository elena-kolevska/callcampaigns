<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * This check prevents companies seeing each other's endpoints,
     * but allows superadmins to see everything
     *
     * @param $company_id
     */
    public function checkRights($company_id)
    {
        if (\Auth::user()->company_id != $company_id AND !\Auth::user()->is_superadmin){
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('You shouldn\'t be requesting this');
        }
    }

}
