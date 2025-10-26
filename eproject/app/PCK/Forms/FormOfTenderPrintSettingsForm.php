<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class FormOfTenderPrintSettingsForm extends FormValidator {

    /**
     * Validation rules for creating a new Resource
     *
     * @var array
     */
    protected $rules = [
        'margin_top'          => 'required|integer|min:0',
        'margin_bottom'       => 'required|integer|min:0',
        'margin_left'         => 'required|integer|min:0',
        'margin_right'        => 'required|integer|min:0',
        'include_header_line' => 'required|integer|min:0|max:1',
        'header_spacing'      => 'required|integer|min:0',
        'footer_font_size'    => 'required|integer|min:0',
        'font_size'           => 'required|integer|min:1',
        'title_text'          => 'required',
    ];

}