<?php
require_once dirname(__FILE__).'/../bootstrap/unit.php';

$configuration = ProjectConfiguration::getApplicationConfiguration( 'backend', 'test', true);

$dbm = new sfDatabaseManager($configuration);
$con = Doctrine_Manager::getInstance()->getConnection('main_conn');

try
{
    $con->beginTransaction();

    $scheduleOfRate = new ScheduleOfRate();

    $scheduleOfRate->name = 'Civil BUR';
    $scheduleOfRate->save($con);

    $trade = new ScheduleOfRateTrade();
    $trade->description = 'SITE CLEARING';
    $trade->schedule_of_rate_id = $scheduleOfRate->id;
    $trade->save($con);

    $buildUpRateIds = array();
    $resource = Doctrine_Core::getTable('Resource')->find(1);

    $item = new ScheduleOfRateItem();
    $item->description = 'Item A';
    $item->type = ScheduleOfRateItem::TYPE_WORK_ITEM;
    $item->trade_id = $trade->id;
    $item->save($con);
    $item->getTable()->getTree()->createRoot($item);

    for($i=1;$i<=5;$i++)
    {
        $buildUpRateItem = new ScheduleOfRateBuildUpRateItem();
        $buildUpRateItem->schedule_of_rate_item_id = $item->id;
        $buildUpRateItem->resource_id = $resource->id;
        $buildUpRateItem->description = 'Build up rate '.$i;
        $buildUpRateItem->priority = $i;
        $buildUpRateItem->save($con);

        $buildUpRateIds[] = $buildUpRateItem->id;

        unset($buildUpRateItem);
    }

    $formulatedColumnTable = Doctrine_Core::getTable('ScheduleOfRateBuildUpRateFormulatedColumn');


    $formulatedColumn_1 = $formulatedColumnTable->getByRelationIdAndColumnName($buildUpRateIds[0], ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);

    $formulatedColumn_1->setFormula('100.2');
    $formulatedColumn_1->save($con);

    $t = new lime_test(12);

    $t->comment('Testing FormulatedColumn with simple decimal number');
    $t->is($formulatedColumn_1->value, '100.2');
    $t->is($formulatedColumn_1->final_value, 100.2);
    $t->is($formulatedColumn_1->column_name, ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);
    $t->is($formulatedColumn_1->relation_id, $buildUpRateIds[0]);

    $formulatedColumn_2 = $formulatedColumnTable->getByRelationIdAndColumnName($buildUpRateIds[1], ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);

    $formulatedColumn_2->setFormula('-8(5/2)^2*(1-sqrt(4))-8');
    $formulatedColumn_2->save($con);

    $t->comment('Testing FormulatedColumn with math expression and function');
    $t->is($formulatedColumn_2->value, '-8(5/2)^2*(1-sqrt(4))-8');
    $t->is($formulatedColumn_2->final_value, 42);
    $t->is($formulatedColumn_2->column_name, ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);
    $t->is($formulatedColumn_2->relation_id, $buildUpRateIds[1]);

    $formulatedColumn_3 = $formulatedColumnTable->getByRelationIdAndColumnName($buildUpRateIds[2], ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);

    $formulatedColumn_3->setFormula('r'.$buildUpRateIds[0].'+'.'r'.$buildUpRateIds[1].'+12000');
    $formulatedColumn_3->save($con);

    $t->comment('Testing FormulatedColumn with invalid expression');
    $t->is($formulatedColumn_3->value, 'r'.$buildUpRateIds[0].'+'.'r'.$buildUpRateIds[1].'+12000');
    $t->is($formulatedColumn_3->final_value, 231.2);
    $t->is($formulatedColumn_3->column_name, ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);
    $t->is($formulatedColumn_3->relation_id, $buildUpRateIds[2]);

    /*$formulatedColumn_2->setFormula('hasghdgas');
    $formulatedColumn_2->save($con);

    $t->is($formulatedColumn_2->final_value, 42);
    $t->is($formulatedColumn_3->final_value, 231.2);*/

    $formulatedColumn_1->setFormula('6+'.'r'.$buildUpRateIds[2].'+89');
    $formulatedColumn_1->save($con);

    $formulatedColumn_1 = $formulatedColumnTable->getByRelationIdAndColumnName($buildUpRateIds[0], ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);

    $t->comment('Testing FormulatedColumn with invalid expression');
    $t->is($formulatedColumn_1->value, '6+r'.$buildUpRateIds[1].'+89');
    $t->is($formulatedColumn_1->final_value, 231.2);
    $t->is($formulatedColumn_3->final_value, 231.2);
    $t->is($formulatedColumn_1->column_name, ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);
    $t->is($formulatedColumn_1->relation_id, $buildUpRateIds[0]);

    $formulatedColumn_4 = $formulatedColumnTable->getByRelationIdAndColumnName($buildUpRateIds[3], ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);

    $formulatedColumn_4->setFormula('2312.67');
    $formulatedColumn_4->save($con);

    $formulatedColumn_5 = $formulatedColumnTable->getByRelationIdAndColumnName($buildUpRateIds[4], ScheduleOfRateBuildUpRateItem::FORMULATED_COLUMN_NUMBER);

    $formulatedColumn_5->setFormula('abcdefg');
    $formulatedColumn_5->save($con);





    $con->commit();
    //$con->rollback();
}
catch(Exception $e)
{
    $con->rollback();
    throw $e;
}



//$root = $node->saveAsLastChildOf(5);

//print_r($root);