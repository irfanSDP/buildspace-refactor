<?php

/**
 * printLayoutSetting actions.
 *
 * @package    buildspace
 * @subpackage printLayoutSetting
 * @author     1337 developers
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class printLayoutSettingActions extends BaseActions {

    public function executeGetSettings(sfWebRequest $request)
    {
        $id = $request->getParameter('id') ? : 1;

        $this->forward404Unless($request->isXmlHttpRequest() AND $settings = Doctrine_Core::getTable('BillLayoutSetting')->getPrintingLayoutSettings($id));

        return $this->renderJson($settings);
    }

    public function executeSaveSettings(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        // check the request and see whether it is a post request or not
        // if not then redirect to 404 page
        $this->forward404Unless($request->isXmlHttpRequest() and $request->isMethod('post'));

        $heads          = array();
        $printSettingId = $request->getParameter('projectId');
        $contents       = ( is_array($request->getParameter('content')) ) ? $request->getParameter('content') : json_decode($request->getParameter('content'), true);
        $type           = $request->getParameter('type');

        // find the project layout setting first
        $masterSetting = Doctrine_Core::getTable('BillLayoutSetting')->find($printSettingId);

        // posted fields that will be translated into fields name inside the database
        switch ($type)
        {
            case 'headStyling':
                break;

            case 'fontNumber':
                $setting = $masterSetting;
                $form    = new BillLayoutSettingForm();

                $fields = array(
                    'fontTypeName'    => 'font',
                    'fontSize'        => 'size',
                    'amtCommaRemove'  => 'comma_total',
                    'rateCommaRemove' => 'comma_rate',
                    'qtyCommaRemove'  => 'comma_qty'
                );
                break;

            case 'pageFormat':
                $setting = $masterSetting;
                $form    = new BillLayoutSettingForm();

                $fields = array(
                    'priceFormat'                => 'priceFormat',
                    'printAmountOnly'            => 'print_amt_col_only',
                    'printNoPrice'               => 'print_without_price',
                    'printFullDecimal'           => 'print_full_decimal',
                    'includePSUM'                => 'add_psum_pcsum',
                    'printDollarCents'           => 'print_dollar_cent',
                    'printNoCents'               => 'print_without_cent',
                    'toggleArgment'              => 'switch_qty_unit_rate',
                    'printElementTitle'          => 'print_element_header',
                    'printElementInGrid'         => 'print_element_grid',
                    'printElementInGridOnce'     => 'print_element_grid_once',
                    'printContdEndDesc'          => 'add_cont',
                    'indentItem'                 => 'indent_item',
                    'includeIandO'               => 'includeIAndOForBillRef',
                    'enableBindingAlignment'     => 'apply_binding_alignment',
                    'contdPrefix'                => 'contd',
                    'pageNumberingOption'        => 'page_numbering_option',
                    'pageNoPrefix'               => 'page_no_prefix',
                    'printDateOfPrinting'        => 'print_date_of_printing',
                    'printGrandTotalQty'         => 'print_grand_total_quantity',
                    'alignElementTitleToTheLeft' => 'align_element_to_left',
                    'closeGrid'                  => 'close_grid',
                );
                break;

            case 'summaryPhrases':
                $setting = $masterSetting->getBillPhrase();
                $form    = new BillLayoutPhraseForm();

                $fields = array(
                    'toCollection'            => 'to_collection',
                    'descHeader'              => 'table_header_description',
                    'unitHeader'              => 'table_header_unit',
                    'qtyHeader'               => 'table_header_qty',
                    'rateHeader'              => 'table_header_rate',
                    'amtHeader'               => 'table_header_amt',
                    'currencyPrefix'          => 'currency',
                    'centPrefix'              => 'cents',
                    'collectionInGridPrefix'  => 'collection_in_grid',
                    'summaryPrefix'           => 'summary',
                    'summaryInGridPrefix'     => 'summary_in_grid',
                    'totalPerUnitPrefix'      => 'totalPerUnitPrefix',
                    'totalUnitPrefix'         => 'totalUnitPrefix',
                    'totalPerTypePrefix'      => 'totalPerTypePrefix',
                    'summaryPageNoPrefix'     => 'summary_page_no',
                    'tenderPrefix'            => 'summary_tender',
                    'summaryPageNumbering[1]' => 'summary_page_one',
                    'summaryPageNumbering[2]' => 'summary_page_two',
                    'summaryPageNumbering[3]' => 'summary_page_three',
                    'summaryPageNumbering[4]' => 'summary_page_four',
                    'summaryPageNumbering[5]' => 'summary_page_five',
                    'summaryPageNumbering[6]' => 'summary_page_six',
                    'summaryPageNumbering[7]' => 'summary_page_seven',
                    'summaryPageNumbering[8]' => 'summary_page_eight',
                    'summaryPageNumbering[9]' => 'summary_page_nine'
                );
                break;

            case 'headerFooter':
                $setting = $masterSetting->getBillPhrase();
                $form    = new BillLayoutPhraseForm();

                $fields = array(
                    'eleHeadBold'       => 'element_header_bold',
                    'eleHeadUnderline'  => 'element_header_underline',
                    'eleHeadItalic'     => 'element_header_italic',
                    'footHeadBold'      => 'element_footer_bold',
                    'footHeadUnderline' => 'element_footer_underline',
                    'footHeadItalic'    => 'element_footer_italic',
                    'topLeftRow1'       => 'element_note_top_left_row1',
                    'topLeftRow2'       => 'element_note_top_left_row2',
                    'topRightRow1'      => 'element_note_top_right_row1',
                    'botLeftRow1'       => 'element_note_bot_left_row1',
                    'botLeftRow2'       => 'element_note_bot_left_row2',
                    'botRightRow1'      => 'element_note_bot_right_row1',
                    'botRightRow2'      => 'element_note_bot_right_row2'
                );
                break;
        }

        // insertion method will be based on which type of data will be entered
        // will be separate to normal insertion and dynamic insertion
        if ( $type !== 'headStyling' AND $type !== 'reserveWords' )
        {
            foreach ( $fields as $key => $field )
            {
                $value             = ( array_key_exists($key, $contents) ) ? $contents[$key] : false;
                $setting->{$field} = ( empty( $value ) ) ? false : $value;

                // store the database field name and value to be validated later
                $validateData[$field] = $value;
            }

            if ( $this->isFormCorrect($validateData, $form) )
            {
                try
                {
                    $setting->save();
                    $success  = true;
                    $errorMsg = null;
                } catch (Exception $e)
                {
                    $errorMsg = $e->getMessage();
                    $success  = false;
                }
            }
            else
            {
                $errorMsg = $form->getErrors();
                $success  = false;
            }
        }
        elseif ( $type === 'headStyling' AND !empty ( $contents ) )
        {
            $con = Doctrine_Core::getTable('BillLayoutHeadSetting')->getConnection();

            try
            {
                $con->beginTransaction();

                foreach ( $contents['id'] as $key => $id )
                {
                    // search existing id, if got then update only
                    // else, insert new record for the time being
                    // will be returning the ID as well for javascript
                    // to post the correct ID when the submit button is
                    // pressed again
                    $headSetting            = Doctrine_Core::getTable('BillLayoutHeadSetting')->find($id);
                    $headSetting            = $headSetting ? $headSetting : new BillLayoutHeadSetting();
                    $headSetting->head      = ( isset ( $contents['head'][$key] ) ) ? $contents['head'][$key] : null;
                    $headSetting->bold      = ( isset ( $contents['bold'][$key] ) ) ? true : false;
                    $headSetting->italic    = ( isset ( $contents['italic'][$key] ) ) ? true : false;
                    $headSetting->underline = ( isset ( $contents['underline'][$key] ) ) ? true : false;
                    $headSetting->save();

                    $heads[$headSetting->head] = $headSetting->id;

                    $headSetting->free();
                }

                $con->commit();
                $success  = true;
                $errorMsg = null;
            } catch (Exception $e)
            {
                $con->rollback();
                $errorMsg = $e->getMessage();
                $success  = false;
            }
        }

        $data = array( 'heads' => $heads, 'success' => $success, 'error' => $errorMsg );

        return $this->renderJson($data);
    }

    // simple method to validate forms
    protected function isFormCorrect($data, sfForm $form)
    {
        $form->bind($data);

        return $form->isValid() ? true : false;
    }

}