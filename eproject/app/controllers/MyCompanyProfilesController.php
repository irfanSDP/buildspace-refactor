<?php

use PCK\Forms\MyCompanyProfileForm;
use PCK\MyCompanyProfiles\MyCompanyProfileRepository;

class MyCompanyProfilesController extends \BaseController {

    private $myCompanyProfileRepo;

    private $form;

    public function __construct(MyCompanyProfileRepository $myCompanyProfileRepo, MyCompanyProfileForm $form)
    {
        $this->myCompanyProfileRepo = $myCompanyProfileRepo;
        $this->form = $form;
    }

    /**
     * Show the form for editing the specified My Company Profile.
     *
     * @return Response
     */
    public function edit()
    {
        $companyProfile = $this->myCompanyProfileRepo->find();

        return View::make('my_company_profiles.edit', compact('companyProfile'));
    }

    /**
     * Update the specified My Company Profile in storage.
     *
     * @return Response
     */
    public function update()
    {
        $companyProfile = $this->myCompanyProfileRepo->find();

        $inputs = Input::all();

        $this->form->validate($inputs);

        $this->myCompanyProfileRepo->update($companyProfile, $inputs);

        if( Input::hasFile('company_logo') )
        {
            $success = $this->myCompanyProfileRepo->updateCompanyLogo($companyProfile, Input::file('company_logo'));

            if( ! $success )
            {
                Flash::error("Oh dear! The file could not be saved. Please make sure the file is an image.");

                return Redirect::back();
            }
        }

        Flash::success('Successfully updated My Company\'s Profile Information');

        return Redirect::back();
    }

}