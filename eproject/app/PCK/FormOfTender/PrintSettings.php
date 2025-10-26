<?php namespace PCK\FormOfTender;

use Illuminate\Database\Eloquent\Model;

class PrintSettings extends Model {

    protected $table = 'form_of_tender_print_settings';

    CONST DEFAULT_FONT_SIZE           = 14;
    CONST DEFAULT_MARGIN              = 10;
    CONST DEFAULT_INCLUDE_HEADER_LINE = true;
    CONST DEFAULT_HEADER_SPACING      = 5;
    CONST DEFAULT_FOOTER_TEXT         = 'FOT / ';
    CONST DEFAULT_FOOTER_FONT_SIZE    = 8;
    const DEFAULT_TITLE               = 'Form of Tender';

    protected $fillable = [
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'include_header_line',
        'header_spacing',
        'footer_text',
        'footer_font_size',
        'font_size',
    ];

    public function tender()
    {
        return $this->belongsTo('PCK\Tenders\Tender');
    }
}