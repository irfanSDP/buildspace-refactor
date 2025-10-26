<style type="text/css">
    <?php echo $stylesheet?>
    <?php echo $layoutStyling?>
    .mainTable tr {background-color:white;color:#000;}
    .mainTable tr.error {background-color:rgba(231,76,60,.88);color:white;}
    .mainTable tr.warning {background-color:rgba(243,156,18,.88);color:white;}
    .errorDetailsContainer {
        padding-right: 12px;
        padding-left: 12px;
        border-radius: 6px;
        padding-top: 12px;
        padding-bottom: 12px;
        margin-bottom: 30px;
        color: inherit;
        background-color: rgba(231,76,60,.88);
    }
    .errorDetailsContainer h1 {
        color:#eee;
        width:auto;
        position:relative;
        left:0;
        right:0;
        line-height:1.1;
        letter-spacing:0;
        text-shadow:none;
        font-size:18px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-weight: 500;
        margin: .67em 0;
    }
    .errorDetailsContainer .errorInfoTable {
        font-size:12px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        border: 1px solid #ddd;
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
    .errorDetailsContainer .errorDetailsTableTitle {text-align:center;}
    .errorInfoTable>tbody>tr>td, .errorInfoTable>tbody>tr>th, .errorInfoTable>thead>tr>td, .errorInfoTable>thead>tr>th {
        padding: 8px;
        line-height: 1.42857143;
    }
</style>
<div class="container" xmlns="http://www.w3.org/1999/html" style="width:auto;margin:50px 100px 40px 100px;">
    <div style="width:450px;padding-right:32px;float:left;">
        <div class="errorDetailsContainer">
            <h1><?php echo $errorMessage?></h1>
            <div style='text-align:left;padding-bottom:12px;font-size:12px;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;'>
                <b>Bill:</b> <?php echo $billItem->Element->ProjectStructure->title?>
            </div>
            <div style='text-align:left;padding-bottom:12px;font-size:12px;font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;'>
                <b>Element:</b> <?php echo $billItem->Element->description?>
            </div>
            <table class="errorInfoTable">
                <thead>
                    <tr>
                        <th class="errorDetailsTableTitle">Full Description</th>
                        <th class="errorDetailsTableTitle" style="width:52px;">Type</th>
                        <th class="errorDetailsTableTitle" style="width:52px;">Row No.</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $billItem->description?></td>
                        <td style="text-align:center;"><?php echo BillItemTable::getItemTypeText($billItem->type)?></td>
                        <td style="text-align:center;"><?php echo $rowIdxInBillManager?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <table cellpadding="0" cellspacing="0" class="mainTable">
        <tr>
            <td class="bqHeadCell" style="min-width:35px;width:35px;">Item</td>
            <td class="bqHeadCell" style="min-width:320px;width:320px;">Description</td>
            <td class="bqHeadCell" style="min-width:50px;width:50px;">Unit</td>
        </tr>
    <?php $rowCount = 1;?>
    <?php foreach($pageItems as $pageItem):?>
    <?php
        $headerClass = ($pageItem[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == SupplyOfMaterialItem::TYPE_HEADER or $pageItem[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_TYPE] == SupplyOfMaterialItem::TYPE_HEADER_N) ? 'bqHead'.$pageItem[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] : null
    ?>
    <tr>
        <td class="bqCounterCell"><?php echo $pageItem[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_ROW_IDX] ?></td>
        <td class="bqDescriptionCell <?php $headerClass?>" style="padding-left:4px;">
            <pre class="<?php echo $headerClass ? $headerClass : 'description'?>"><?php echo trim($pageItem[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_DESCRIPTION])?></pre>
        </td>
        <td class="bqUnitCell"><?php echo $pageItem[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_UNIT] ?></td>
    </tr>
    <?php $rowCount++?>
    <?php endforeach?>
    <?php $occupiedRowsIdx = 0; foreach($occupiedRows as $occupiedRowDesc):?>
        <tr class="<?php echo ($maxRows < $rowCount) ? 'error' : ''; ?>">
            <td class="bqCounterCell"></td>
            <td class="bqDescriptionCell" style="padding-left:4px;">
                <?php
                    $preClass = ($billItem->type == SupplyOfMaterialItem::TYPE_HEADER or $billItem->type == SupplyOfMaterialItem::TYPE_HEADER_N) ? 'bqHead'.$billItem->level : 'description'
                ?>
                <pre class="<?php echo $preClass?>"><?php echo trim($occupiedRowDesc)?></pre>
            </td>
            <td class="bqUnitCell"><?php echo ($occupiedRowsIdx==($occupiedRows->count()-1)) ? $billItem->UnitOfMeasurement->symbol : "&nbsp;" ?></td>
        </tr>
    <?php $occupiedRowsIdx++; $rowCount++?>
    <?php endforeach;?>
    <tr>
        <td class="bqCounterCell" style="border-bottom:1px solid #000;"></td>
        <td class="bqDescriptionCell" style="border-bottom:1px solid #000;"></td>
        <td class="bqUnitCell" style="border-bottom:1px solid #000;">&nbsp;</td>
    </tr>
    </table>
    <p>Page No. <?php echo $pageNumber ?></p>
</div>