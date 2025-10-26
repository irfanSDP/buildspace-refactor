<?php

use PCK\Licenses\LicenseRepository;

class LicenseController extends \BaseController
{
    private $repository;

    public function __construct(LicenseRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $licenseDetails = $this->repository->getLicenseDetails();
        $isLicenseValid = false;

        if($licenseDetails)
        {
            $isLicenseValid = $this->repository->checkLicenseValidity();
        }

        return View::make('licenses.index', [
            'licenseDetails' => $licenseDetails,
            'isLicenseValid' => $isLicenseValid,
        ]);
    }

    public function store()
    {
        $success = $this->repository->storeLicense(Input::all());

        if($success)
        {
            Flash::success(trans('licenses.activationSuccess'));
        }
        else
        {
            Flash::error(trans('licenses.activationFailure'));
        }

        return Redirect::route('license.index');
    }

    
}

