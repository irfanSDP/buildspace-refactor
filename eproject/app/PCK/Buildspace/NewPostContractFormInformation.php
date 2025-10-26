<?php namespace PCK\Buildspace;

use Illuminate\Database\Eloquent\Model;

class NewPostContractFormInformation extends Model {

    protected $connection = 'buildspace';

    protected $table = 'bs_new_post_contract_form_information';

    public function projectStructure()
    {
        return $this->belongsTo('PCK\Buildspace\Project', 'project_structure_id');
    }

    const TYPE_1      = 1;
    const TYPE_1_TEXT = 'Letter of Award';
    const TYPE_1_CODE = 'LA';
    const TYPE_2      = 2;
    const TYPE_2_TEXT = 'Work Order';
    const TYPE_2_CODE = 'WO';
    const TYPE_3      = 3;
    const TYPE_3_TEXT = 'Contract Info';
    const TYPE_3_CODE = 'CI';

    const WAIVER_OPTION_TYPE_E_TENDER = 1;
    const WAIVER_OPTION_TYPE_E_AUCTION = 2;

    const E_TENDER_WAIVER_OPTION_SITE_URGENCY = 1;
    const E_TENDER_WAIVER_OPTION_SITE_URGENCY_TEXT = 'Site Urgency';
    const E_TENDER_WAIVER_OPTION_INTER_COMPANY = 2;
    const E_TENDER_WAIVER_OPTION_INTER_COMPANY_TEXT = 'Inter-Company';
    const E_TENDER_WAIVER_OPTION_OTHERS = 4;
    const E_TENDER_WAIVER_OPTION_OTHERS_TEXT = 'Others';

    const E_AUCTION_WAIVER_OPTION_SITE_URGENCY = 8;
    const E_AUCTION_WAIVER_OPTION_SITE_URGENCY_TEXT = 'Site Urgency';
    const E_AUCTION_WAIVER_OPTION_INTER_COMPANY = 16;
    const E_AUCTION_WAIVER_OPTION_INTER_COMPANY_TEXT = 'Inter-Company';
    const E_AUCTION_WAIVER_OPTION_OTHERS = 32;
    const E_AUCTION_WAIVER_OPTION_OTHERS_TEXT = 'Others';

    public static function getTypeText($type)
    {
        $types = array(
            self::TYPE_1 => self::TYPE_1_TEXT,
            self::TYPE_2 => self::TYPE_2_TEXT,
            self::TYPE_3 => self::TYPE_3_TEXT,
        );

        return $types[ $type ] ?? null;
    }

    public static function getTypeCode($type)
    {
        $types = array(
            self::TYPE_1 => self::TYPE_1_CODE,
            self::TYPE_2 => self::TYPE_2_CODE,
            self::TYPE_3 => self::TYPE_3_CODE,
        );

        return $types[ $type ] ?? null;
    }

    public static function getWaiverTypeText($type)
    {
        $types = [
            self::E_TENDER_WAIVER_OPTION_SITE_URGENCY   => self::E_TENDER_WAIVER_OPTION_SITE_URGENCY_TEXT,
            self::E_TENDER_WAIVER_OPTION_INTER_COMPANY  => self::E_TENDER_WAIVER_OPTION_INTER_COMPANY_TEXT,
            self::E_TENDER_WAIVER_OPTION_OTHERS         => self::E_TENDER_WAIVER_OPTION_OTHERS_TEXT,
            self::E_AUCTION_WAIVER_OPTION_SITE_URGENCY  => self::E_AUCTION_WAIVER_OPTION_SITE_URGENCY_TEXT,
            self::E_AUCTION_WAIVER_OPTION_INTER_COMPANY => self::E_AUCTION_WAIVER_OPTION_INTER_COMPANY_TEXT,
            self::E_AUCTION_WAIVER_OPTION_OTHERS        => self::E_AUCTION_WAIVER_OPTION_OTHERS_TEXT,
        ];

        return $types[$type] ?? null;
    }
}