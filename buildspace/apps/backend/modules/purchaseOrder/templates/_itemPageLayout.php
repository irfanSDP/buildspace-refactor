<?php $rowTaken = 0; ?>

<div class="logo-header">
    <table style="width: 100%;">
        <tr>
            <td style="width:50%;">
                <span style="display: block; font-weight: bold; margin: 0 0 8px; font-size: 18px;"><?php echo empty($myCompanyName) ? "&nbsp;" : $myCompanyName; ?></span>

                <?php echo empty($poInformation->company_address_1) ? "&nbsp;" : $poInformation->company_address_1; ?><br>
                <?php echo empty($poInformation->company_address_2) ? "&nbsp;" : $poInformation->company_address_2; ?><br>
                <?php echo empty($poInformation->company_address_3) ? "&nbsp;" : $poInformation->company_address_3; ?>
            </td>
            <td style="width:45%;font-weight:bold;vertical-align:middle;font-size: 22px;text-align:right;">Purchase Order</td>
        </tr>
    </table>
</div>

<div class="content">
    <table class="supplierInformationTable">
        <tr>
            <td style="width:50%;padding:8px 0;"><span class="bold">Ref:</span> <?php echo $poInformation->ref; ?></td>
            <td style="width:45%;padding:8px 0;"><span class="bold">Date:</span> <?php echo date('d F Y', strtotime($date)); ?><br></td>
        </tr>
        <tr>
            <td style="width:50%;padding:8px 0;"><span class="bold">Your Quo Ref:</span> <?php echo $poInformation->quo_ref; ?><br></td>
            <td style="width:45%;padding:8px 0;"><span class="bold">PO No:</span> <?php echo $poReferenceNo; ?><br></td>
        </tr>
        <tbody>
            <tr>
                <td style="width:45%; padding: 8px 20px 8px 0;">
                    <span class="bold">Supplier Address:</span><br>
                    <span style="font-weight:bold;display: block; line-height:14pt;"><?php echo empty($supplierName) ? "&nbsp;" : $supplierName; ?></span>
                    <span style="display: block; border-bottom: 1px dotted black; line-height:14pt;"><?php echo empty($poInformation->supplier_address_1) ? "&nbsp;" : $poInformation->supplier_address_1; ?></span>
                    <span style="display: block; border-bottom: 1px dotted black; line-height:14pt;"><?php echo empty($poInformation->supplier_address_2) ? "&nbsp;" : $poInformation->supplier_address_2; ?></span>
                    <span style="display: block; border-bottom: 1px dotted black; line-height:14pt;"><?php echo empty($poInformation->supplier_address_3) ? "&nbsp;" : $poInformation->supplier_address_3; ?></span>
                </td>
                <td style="width:45%;padding:8px 0;">
                    <span class="bold">Ship To:</span><br>
                    <span style="display: block; line-height:14pt;">&nbsp;</span>
                    <span style="display: block; border-bottom: 1px dotted black; line-height:14pt;"><?php echo empty($poInformation->ship_to_1) ? "&nbsp;" : $poInformation->ship_to_1; ?></span>
                    <span style="display: block; border-bottom: 1px dotted black; line-height:14pt;"><?php echo empty($poInformation->ship_to_2) ? "&nbsp;" : $poInformation->ship_to_2; ?></span>
                    <span style="display: block; border-bottom: 1px dotted black; line-height:14pt;"><?php echo empty($poInformation->ship_to_3) ? "&nbsp;" : $poInformation->ship_to_3; ?></span>
                </td>
            </tr>
        </tbody>
        <tr>
            <td style="width:50%;padding:4px 0;"><span class="bold">ATTN:</span> <?php echo $poInformation->attention_to; ?></td>
        </tr>
    </table>

    <table cellpadding="0" cellspacing="0" class="mainTable">
        <tr>
            <td class="headCell" style="min-width:50px;width:50px;height:30px;">Item</td>
            <td class="headCell" style="min-width:320px;width:320px;height:30px;">Description</td>
            <td class="headCell" style="min-width:80px;width:80px;height:30px;">Qty</td>
            <td class="headCell" style="min-width:80px;width:80px;height:30px;">Unit</td>
            <td class="headCell" style="min-width:100px;width:100px;height:30px;">Rate</td>
            <td class="headCell" style="min-width:120px;width:120px;height:30px;">Amount</td>
        </tr>
        <?php for ( $i = 0; $i <= $MAX_ROWS; $i++):
            $itemRow = array_key_exists($i, $itemPage) ? $itemPage[$i] : false;

            $headerClass = null;
            $headerStyle = null;

            $ref  = $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_CHAR_REF] ?: null;
            $desc = $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_DESCRIPTION] ?: null;
            $unit = $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_UNIT] ?: null;
            $rate = $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_RATE];
            $qty  = $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_QUANTITY];
            $amt  = $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_AMOUNT];

            if ($itemRow and $itemRow[sfBuildspacePurchaseOrderGenerator::ITEM_PROPERTY_TYPE] == ResourceItem::TYPE_HEADER)
            {
                $headerClass = 'bqHead';
                $headerStyle = null;
            }

            $rowTaken++;
        ?>
        <tr>
            <td class="referenceCharCell"><?php echo $ref; ?></td>
            <td class="descriptionCell <?php echo $headerClass?>">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="margin: 6px 0;'.$headerStyle.'">'.trim($desc).'</pre>' : null?>
            </td>
            <td class="amountCell"><?php echo (empty((float) $qty)) ? null : number_format($qty, 2, $priceFormat[0], $priceFormat[1]); ?></td>
            <td class="unitCell"><?php echo $unit; ?></td>
            <td class="amountCell"><?php echo (empty((float) $rate)) ? null : number_format($rate, $printWithoutCents, $priceFormat[0], $priceFormat[1]); ?></td>
            <td class="amountCell"><?php echo (empty((float) $amt)) ? null : number_format($amt, $printWithoutCents, $priceFormat[0], $priceFormat[1]); ?></td>
        </tr>
        <?php endfor; ?>

        <?php if ( $isLastPage ) {
            include_partial('purchaseOrder/footerLayout', array(
                'continuePage'      => $continuePage,
                'taxes'             => $poTaxes,
                'grandTotal'        => $grandTotal,
                'poInformation'     => $poInformation,
                'pageCount'         => $pageCount,
                'lastPageCount'     => $lastPageCount,
                'currencyCode'      => $currencyCode,
                'rowTaken'          => $rowTaken,
                'MAX_ROWS'          => $MAX_ROWS,
                'priceFormat'       => $priceFormat,
                'printWithoutCents' => $printWithoutCents,
            ));
        } else { ?>
            <tr>
                <td class="footer" colspan="5" style="border:1px solid #000;padding:5px 3px;text-align:right;">
                    <?php if ( $continuePage ): ?>
                        Current Total Including Total from Previous Page (<?php echo $currencyCode; ?>):
                    <?php else: ?>
                        Total (<?php echo $currencyCode; ?>):
                    <?php endif; ?>
                </td>
                <td class="footer" style="border:1px solid #000;padding:5px 3px;text-align:right;">
                    <?php echo (empty((float) $pageTotal)) ? null : number_format($pageTotal, $printWithoutCents, $priceFormat[0], $priceFormat[1]); ?>
                </td>
            </tr>
            <tr>
                <td class="pageNumberCell" colspan="6" style="padding: 20px 0 0 0;line-height:12px;vertical-align:text-bottom">
                    Page <?php echo $pageCount; ?> of <?php echo $lastPageCount; ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>