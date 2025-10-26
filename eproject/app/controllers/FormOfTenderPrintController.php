<?php

use PCK\FormOfTender\FormOfTenderRepository;
use PCK\Helpers\PdfHelper;
use PCK\FormOfTender\FormOfTender;
use PCK\Tenders\Tender;

class FormOfTenderPrintController extends \BaseController {

    protected $repository;

    const TEMPLATE_CURRENCY_SYMBOL = '$';

    public function __construct(FormOfTenderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Appends all relevant file contents for the form header into a string.
     *
     * @param $allDetails
     *
     * @return string
     */
    public function getHeaderHtml($allDetails)
    {
        $headerStyle = file_get_contents('../app/views/form_of_tender/print/header_layout_style.html');

        $headerStyle .= $allDetails['settings']->include_header_line ? '<hr/>' : '';
        
        return str_replace(['<!--titleText-->', '<!--headerText-->', '<!--headerTextFontSize-->'], [
            trim(strip_tags($allDetails['settings']->title_text)),
            trim(strip_tags($allDetails['header']->header_text, '<p><div>')),
            $allDetails['settings']->font_size
        ], $headerStyle);
    }

    /**
     * Generates wkhtmltopdf options for PDF.
     *
     * @param $allDetails
     * @param $headerHeightInPixels
     *
     * @return string
     */
    public function generatePdfOptions($allDetails, $headerHeightInPixels)
    {
        $headerHeightInPixels = intval($headerHeightInPixels);
        $marginTop            = $allDetails['settings']->margin_top + $headerHeightInPixels / 3;
        $marginBottom         = $allDetails['settings']->margin_bottom;
        $marginLeft           = $allDetails['settings']->margin_left;
        $marginRight          = $allDetails['settings']->margin_right;

        $marginTopOption    = ' --margin-top ' . $marginTop;
        $marginBottomOption = ' --margin-bottom ' . $marginBottom;
        $marginLeftOption   = ' --margin-left ' . $marginLeft;
        $marginRightOption  = ' --margin-right ' . $marginRight;

        $headerSpacing = $allDetails['settings']->header_spacing;
        $headerOptions = ' --header-spacing ' . $headerSpacing;

        $footerFontSize = $allDetails['settings']->footer_font_size;
        $footerText     = '"' . $allDetails['settings']->footer_text . ' [page]"';
        $footerOptions  = ' --footer-center ' . $footerText . ' --footer-font-size ' . $footerFontSize;

        return ' --encoding utf-8 --disable-smart-shrinking ' . $footerOptions . $headerOptions . $marginBottomOption . $marginTopOption . $marginRightOption . $marginLeftOption;
    }

    /**
     * Because the auto calculation of height for wkhtmltopdf's --header-html is broken, a static value has to be determined for the --margin-top option.
     * We render the header in a separate view, get its approximate height, and use that calculated height for the --margin-top value.
     * The view immediately redirects to the new view that uses the calculated height (h parameter in the url) for the --margin-top value.
     * Rather hackish and only works 'well enough', change once a proper solution is found.
     *
     * @param $project
     * @param $tenderId
     *
     * @return \Illuminate\View\View
     */
    public function processFormOfTender($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        $allDetails             = array();
        $allDetails['header']   = $tender->formOfTender->header;
        $allDetails['settings'] = $tender->formOfTender->printSettings;

        $content = $this->getHeaderHtml($allDetails);

        $content = PdfHelper::removeBreaksFromHtml($content);

        $routeGenerate = route('form_of_tender.generate', array( $project->id, $tenderId ));

        return View::make('form_of_tender.print.getHeight', array(
            'project'       => $project,
            'content'       => $content,
            'settings'      => $allDetails['settings'],
            'routeGenerate' => $routeGenerate,
        ));
    }

    /**
     * Generates the Form Of Tender in PDF.
     *
     * @param $project
     * @param $tenderId
     *
     * @return bool
     */
    public function generateFormOfTender($project, $tenderId)
    {
        $tender = Tender::find($tenderId);

        $allDetails         = $this->repository->getAllComponents($tender->formOfTender->id);
        $addendumFolders    = $this->repository->getTenderAddendumFolders($tenderId);
        $tenderAlternatives = $this->repository->getPrintTenderAlternativesBeforeContractorInput($tenderId);

        PDF::setHeaderHtml($this->getHeaderHtml($allDetails));

        PDF::setOptions($this->generatePdfOptions($allDetails, Input::get('h')));

        return PDF::html('form_of_tender.print.layout', array(
            'allDetails'         => $allDetails,
            'addendaStartNumber' => $allDetails['clauses']->count() + 1,
            'addendumFolders'    => $addendumFolders,
            'tenderAlternatives' => $tenderAlternatives,
            'currencySymbol'     => $project->modified_currency_code,
        ));
    }

    /**
     * ** This is a HACK **
     * Because the auto calculation of height for wkhtmltopdf's --header-html is broken, a static value has to be determined for the --margin-top option.
     * We render the header in a separate view, get its approximate height, and use that calculated height for the --margin-top value.
     * The view immediately redirects to the new view that uses the calculated height (h parameter in the url) for the --margin-top value.
     * Rather hackish and only works 'well enough', change once a proper solution is found.
     *
     * @return \Illuminate\View\View
     */
    public function processTemplateFormOfTender($templateId)
    {
        $formOfTender = FormOfTender::find($templateId);

        $allDetails             = array();
        $allDetails['header']   = $formOfTender->header;
        $allDetails['settings'] = $formOfTender->printSettings;

        $content = $this->getHeaderHtml($allDetails);

        $content = PdfHelper::removeBreaksFromHtml($content);

        $routeGenerate = route('form_of_tender.template.generate', [$templateId]);

        return View::make('form_of_tender.print.getHeight', array(
            'content'       => $content,
            'settings'      => $allDetails['settings'],
            'routeGenerate' => $routeGenerate,
            'isTemplate'    => true,
        ));
    }

    /**
     * Generates the Form Of Tender in PDF.
     *
     * @return bool
     */
    public function generateTemplateFormOfTender($templateId)
    {
        $allDetails         = $this->repository->getAllComponents($templateId);
        $addendumFolders    = array();
        $tenderAlternatives = $this->repository->getPrintTenderAlternativesTemplate($templateId);

        PDF::setHeaderHtml($this->getHeaderHtml($allDetails));

        PDF::setOptions($this->generatePdfOptions($allDetails, Input::get('h')));

        return PDF::html('form_of_tender.print.layout', array(
            'allDetails'         => $allDetails,
            'addendaStartNumber' => $allDetails['clauses']->count() + 1,
            'addendumFolders'    => $addendumFolders,
            'tenderAlternatives' => $tenderAlternatives,
            'currencySymbol'     => self::TEMPLATE_CURRENCY_SYMBOL,
        ));
    }

    /**
     * Because the auto calculation of height for wkhtmltopdf's --header-html is broken, a static value has to be determined for the --margin-top option.
     * We render the header in a separate view, get its approximate height, and use that calculated height for the --margin-top value.
     * The view immediately redirects to the new view that uses the calculated height (h parameter in the url) for the --margin-top value.
     * Rather hackish and only works 'well enough', change once a proper solution is found.
     *
     * @param $project
     * @param $tenderId
     *
     * @param $companyId
     *
     * @return \Illuminate\View\View
     */
    public function processContractorFormOfTender($project, $tenderId, $companyId)
    {
        $tender = Tender::find($tenderId);
        $allDetails             = array();
        $allDetails['header']   = $tender->formOfTender->header;
        $allDetails['settings'] = $tender->formOfTender->printSettings;

        $content = $this->getHeaderHtml($allDetails);

        $content = PdfHelper::removeBreaksFromHtml($content);

        $routeGenerate = route('form_of_tender.contractorInput.generate', array( $project->id, $tenderId, $companyId ));

        return View::make('form_of_tender.print.getHeight', array(
            'content'       => $content,
            'settings'      => $allDetails['settings'],
            'routeGenerate' => $routeGenerate,
        ));
    }

    /**
     * Generates the Form Of Tender in PDF.
     *
     * @param $project
     * @param $tenderId
     *
     * @param $companyId
     *
     * @return bool
     */
    public function generateContractorFormOfTender($project, $tenderId, $companyId)
    {
        $tender = Tender::find($tenderId);

        $allDetails         = $this->repository->getAllComponents($tender->formOfTender->id);
        $addendumFolders    = $this->repository->getTenderAddendumFolders($tenderId);
        $tenderAlternatives = $this->repository->getPrintTenderAlternativesAfterContractorInput($project->id, $tenderId, $companyId);

        PDF::setHeaderHtml($this->getHeaderHtml($allDetails));

        PDF::setOptions($this->generatePdfOptions($allDetails, Input::get('h')));

        return PDF::html('form_of_tender.print.layout', array(
            'allDetails'         => $allDetails,
            'addendaStartNumber' => $allDetails['clauses']->count() + 1,
            'addendumFolders'    => $addendumFolders,
            'tenderAlternatives' => $tenderAlternatives,
            'currencySymbol'     => $project->modified_currency_code,
        ));
    }

}