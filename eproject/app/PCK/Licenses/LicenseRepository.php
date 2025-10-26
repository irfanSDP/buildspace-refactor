<?php namespace PCK\Licenses;

use Carbon\Carbon;
use PCK\Companies\Company;

class LicenseRepository
{
    public function getLicenseDetails()
    {
        $licenseDetails = null;
        $currentActiveLicense = License::getActiveLicense();

        if($currentActiveLicense)
        {
            $licenseDetails = unserialize(License::safeDecrypt($currentActiveLicense->license_key, base64_decode($currentActiveLicense->decryption_key)));
            $licenseValidUntilDateTime = Carbon::parse($licenseDetails['validUntilDateTime']);
    
            $licenseDetails['licenseKey']         = $currentActiveLicense->license_key;
            $licenseDetails['decryptionKey']      = $currentActiveLicense->decryption_key;
            $licenseDetails['validUntilDateTime'] = $licenseValidUntilDateTime->format('l jS \\of F Y h:i:s A');
            $licenseDetails['daysRemaining']      = $licenseValidUntilDateTime->diffInDays(Carbon::now());
        }
        
        return $licenseDetails;
    }

    public function checkLicenseValidity()
    {
        $licensingDisabled = (getenv('disable_licensing') === '1');
        
        if($licensingDisabled) return true;

        $licenseDetails = $this->getLicenseDetails();

        if(is_null($licenseDetails)) return false;

        return License::checkValidity($licenseDetails['licenseKey'], $licenseDetails['decryptionKey']);
    }

    public function checkCompanyLimitHasBeenReached()
    {
        $licensingDisabled = (getenv('disable_licensing') === '1');

        if($licensingDisabled) return false;
        
        $licenseDetails = $this->getLicenseDetails();
        $companyCount = Company::where('confirmed', '=', true)->count();
        
        return $companyCount >= $licenseDetails['companyLimit'];
    }

    public function storeLicense($inputs)
    {
        $success = false;
        $licenseKey = trim($inputs['licenseKey']);
        $decryptionKey = License::getDecryptionKey($licenseKey);

        if(is_null($decryptionKey))
        {
            return false;
        }

        $isLicenseValid = License::checkValidity($licenseKey, $decryptionKey); 
        
        if($isLicenseValid)
        {
            $currentActiveLicense = License::getActiveLicense();

            $newLicense = new License();
            $newLicense->license_key = $licenseKey;
            $newLicense->decryption_key = $decryptionKey;
            $newLicense->save();

            if($currentActiveLicense)
            {
                $currentActiveLicense->delete();
            }

            $success = true;
        }

        return $success;
    }
}

