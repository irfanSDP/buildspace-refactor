<?php namespace PCK\Companies;

use PCK\Buildspace\Region as BsRegion;
use PCK\Buildspace\SubRegion as BsSubRegion;
use PCK\Buildspace\Company as BuildSpaceCompany;

trait BsCompanyTrait {

    public function getBsCompany()
    {
        return BuildSpaceCompany::find($this->reference_id);
    }

    private function updateBSCompany()
    {
        $this->load('country', 'state');

        $bsCompany = $this->getBsCompany();
        $bsCompany = $bsCompany ?: new BuildSpaceCompany();

        $telephoneNumber = preg_replace('/\s\s+/', ' ', $this->telephone_number);//remove multiple spaces
        $telephoneNumber = substr($telephoneNumber, 0, 20);

        $bsCompany->reference_id               = $this->reference_id;
        $bsCompany->name                       = $this->name;
        $bsCompany->registration_no            = $this->reference_no;
        $bsCompany->contact_person_name        = $this->main_contact;
        $bsCompany->contact_person_email       = $this->email;
        $bsCompany->contact_person_direct_line = $telephoneNumber;
        $bsCompany->address                    = $this->address;
        $bsCompany->phone_number               = $telephoneNumber;
        $bsCompany->fax_number                 = $this->fax_number;
        $bsCompany->updated_at                 = $this->updated_at;

        if( ! $bsCompany->exists )
        {
            $bsCompany->shortname  = substr($this->name, 0, 20);
            $bsCompany->created_at = $this->created_at;
        }

        //sync company into BuildSpace
        $country = $this->country;
        $state   = $this->state;

        $region = BsRegion::whereRaw("LOWER(country) = '" . mb_strtolower(trim($country->country)) . "'")
            ->first();

        if( ! $region )
        {
            $region                = new BsRegion();
            $region->iso           = $country->iso;
            $region->iso3          = $country->iso3;
            $region->fips          = $country->fips;
            $region->country       = trim($country->country);
            $region->continent     = $country->continent;
            $region->currency_code = $country->currency_code;
            $region->currency_name = $country->currency_name;
            $region->phone_prefix  = $country->phone_prefix;
            $region->postal_code   = $country->postal_code;
            $region->languages     = $country->languages;
            $region->geonameid     = $country->geonameid;

            $region->save();
        }

        $subRegion = BsSubRegion::whereRaw("LOWER(name) = '" . mb_strtolower(trim($state->name)) . "'")
            ->where('region_id', $region->id)
            ->first();

        if( ! $subRegion )
        {
            $subRegion            = new BsSubRegion();
            $subRegion->name      = trim($state->name);
            $subRegion->timezone  = $state->timezone;
            $subRegion->region_id = $region->id;

            $subRegion->save();
        }

        $bsCompany->region_id     = $region->id;
        $bsCompany->sub_region_id = $subRegion->id;

        $bsCompany->save();
    }

    private function deleteBSCompany()
    {
        if( $bsCompany = $this->getBsCompany() )
        {
            $bsCompany->delete();
        }
    }

}