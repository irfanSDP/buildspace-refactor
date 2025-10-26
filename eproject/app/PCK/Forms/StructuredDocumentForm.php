<?php namespace PCK\Forms;

use Laracasts\Validation\FormValidator;

class StructuredDocumentForm extends FormValidator {

    /**
     * Validation rules for creating a new Resource
     *
     * @var array
     */
    protected $rules = [
        'title'         => 'max:100',
        'heading'       => 'max:500',
        'margin_top'    => 'required|numeric|min:0',
        'margin_bottom' => 'required|numeric|min:0',
        'margin_left'   => 'required|numeric|min:0',
        'margin_right'  => 'required|numeric|min:0',
        'footer_text'   => 'max:20',
        'font_size'     => 'required|numeric|min:5|max:30',
    ];

}