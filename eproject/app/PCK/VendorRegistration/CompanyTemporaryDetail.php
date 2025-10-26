<?php namespace PCK\VendorRegistration;

use Illuminate\Database\Eloquent\Model;
use PCK\BuildingInformationModelling\BuildingInformationModellingLevel;

class CompanyTemporaryDetail extends Model
{
    protected $table = 'company_temporary_details';

    protected $fillable = [
        'address',
        'main_contact',
        'tax_registration_no',
        'email',
        'telephone_number',
        'fax_number',
        'company_status',
        'bumiputera_equity',
        'non_bumiputera_equity',
        'foreigner_equity',
        'cidb_grade',
        'bim_level_id',
        'name',
        'country_id',
        'state_id',
        'reference_no',
    ];

    public function vendorRegistration()
    {
        return $this->belongsTo('PCK\VendorRegistration\VendorRegistration');
    }

    public static function findRecord(VendorRegistration $vendorRegistration)
    {
        return self::where('vendor_registration_id', $vendorRegistration->id)->first();
    }

    public function bimLevel()
    {
        return $this->belongsTo(BuildingInformationModellingLevel::class, 'bim_level_id');
    }

    public function getCompanyWithDraftData()
    {
        $company = $this->vendorRegistration->company;

        $company->name                  = $this->name;
        $company->address               = $this->address;
        $company->main_contact          = $this->main_contact;
        $company->tax_registration_no   = $this->tax_registration_no;
        $company->email                 = $this->email;
        $company->telephone_number      = $this->telephone_number;
        $company->fax_number            = $this->fax_number;
        $company->country_id            = $this->country_id;
        $company->state_id              = $this->state_id;
        $company->reference_no          = $this->reference_no;
        $company->company_status        = $this->company_status;
        $company->bumiputera_equity     = $this->bumiputera_equity;
        $company->non_bumiputera_equity = $this->non_bumiputera_equity;
        $company->foreigner_equity      = $this->foreigner_equity;
        $company->cidb_grade            = $this->cidb_grade;
        $company->bim_level_id          = $this->bim_level_id;

        return $company;
    }

    public static function init(VendorRegistration $vendorRegistration)
    {
        $record = self::findRecord($vendorRegistration);

        if( $record ) return $record;

        $company = $vendorRegistration->company;

        $record = new self;

        $record->vendor_registration_id = $vendorRegistration->id;

        $record->name                  = $company->name;
        $record->address               = $company->address;
        $record->main_contact          = $company->main_contact;
        $record->tax_registration_no   = $company->tax_registration_no;
        $record->email                 = $company->email;
        $record->telephone_number      = $company->telephone_number;
        $record->fax_number            = $company->fax_number;
        $record->country_id            = $company->country_id;
        $record->state_id              = $company->state_id;
        $record->reference_no          = $company->reference_no;
        $record->company_status        = $company->company_status;
        $record->bumiputera_equity     = $company->bumiputera_equity;
        $record->non_bumiputera_equity = $company->non_bumiputera_equity;
        $record->foreigner_equity      = $company->foreigner_equity;
        $record->cidb_grade            = $company->cidb_grade;
        $record->bim_level_id          = $company->bim_level_id;

        $record->save();
    }

    public function updateValues($inputs)
    {
        $this->name                  = trim($inputs['name'] ?? "");
        $this->address               = trim($inputs['address'] ?? "");
        $this->main_contact          = trim($inputs['main_contact'] ?? "");
        $this->tax_registration_no   = trim($inputs['tax_registration_number'] ?? "");
        $this->email                 = trim($inputs['email'] ?? "");
        $this->telephone_number      = trim($inputs['telephone_number'] ?? "");
        $this->fax_number            = trim($inputs['fax_number'] ?? "");

        if(isset($inputs['country_id']))
        {
            $this->country_id = $inputs['country_id'];
        }

        if(isset($inputs['state_id']))
        {
            $this->state_id = $inputs['state_id'];
        }

        if(isset($inputs['reference_no']))
        {
            $this->reference_no = trim($inputs['reference_no'] ?? "");
        }

        $this->company_status        = $inputs['company_status'];
        $this->bumiputera_equity     = trim($inputs['bumiputera_equity'] ?? "");
        $this->non_bumiputera_equity = trim($inputs['non_bumiputera_equity'] ?? "");
        $this->foreigner_equity      = trim($inputs['foreigner_equity'] ?? "");
        $this->cidb_grade            = isset($inputs['cidb_grade']) ? $inputs['cidb_grade'] : null;
        $this->bim_level_id          = isset($inputs['bim_level_id']) ? $inputs['bim_level_id'] : null;

        $this->save();
    }

    public function applyChanges()
    {
        $company = $this->vendorRegistration->company;

        $company->name                  = $this->name;
        $company->address               = $this->address;
        $company->main_contact          = $this->main_contact;
        $company->tax_registration_no   = $this->tax_registration_no;
        $company->email                 = $this->email;
        $company->telephone_number      = $this->telephone_number;
        $company->fax_number            = $this->fax_number;
        $company->country_id            = $this->country_id;
        $company->state_id              = $this->state_id;
        $company->reference_no          = $this->reference_no;
        $company->company_status        = $this->company_status;
        $company->bumiputera_equity     = $this->bumiputera_equity;
        $company->non_bumiputera_equity = $this->non_bumiputera_equity;
        $company->foreigner_equity      = $this->foreigner_equity;
        $company->cidb_grade            = $this->cidb_grade;
        $company->bim_level_id          = $this->bim_level_id;

        $company->save();

        $this->delete();
    }
}