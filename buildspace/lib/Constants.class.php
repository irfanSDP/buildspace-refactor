<?php
class Constants
{
    const DAY_SUNDAY = 0;
    const DAY_MONDAY = 1;
    const DAY_TUESDAY = 2;
    const DAY_WEDNESDAY = 3;
    const DAY_THURSDAY = 4;
    const DAY_FRIDAY = 5;
    const DAY_SATURDAY = 6;

    const DAY_SUNDAY_TEXT = 'SUNDAY';
    const DAY_MONDAY_TEXT = 'MONDAY';
    const DAY_TUESDAY_TEXT = 'TUESDAY';
    const DAY_WEDNESDAY_TEXT = 'WEDNESDAY';
    const DAY_THURSDAY_TEXT = 'THURSDAY';
    const DAY_FRIDAY_TEXT = 'FRIDAY';
    const DAY_SATURDAY_TEXT = 'SATURDAY';

    const HIERARCHY_TYPE_HEADER = 1;
    const HIERARCHY_TYPE_WORK_ITEM = 2;
    const HIERARCHY_TYPE_NOID = 4;

    const HIERARCHY_TYPE_HEADER_TEXT = 'HEAD';
    const HIERARCHY_TYPE_WORK_ITEM_TEXT = 'ITEM';
    const HIERARCHY_TYPE_NOID_TEXT = 'NOID';

    const ARITHMETIC_OPERATOR_ADDITION = '+';
    const ARITHMETIC_OPERATOR_SUBTRACTION = '-';
    const ARITHMETIC_OPERATOR_MULTIPLICATION = '*';
    const ARITHMETIC_OPERATOR_DIVISION = '/';
    const ARITHMETIC_OPERATOR_MODULUS = '%';

    const GRID_LAST_ROW = 'LAST_ROW';

    const MAX_PDO_QUERY_PARAMETERS = 0xFFFF; //Size of unsigned int = 16bits. Value = 65535.
}
?>
