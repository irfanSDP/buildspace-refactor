<?php

class CalculateBuildUpRateTotal extends Task{

    protected $implodedIds;

    public function __construct($params)
    {
        $this->implodedIds = $params;
    }

    function run(){
        $configuration = ProjectConfiguration::getApplicationConfiguration( 'backend', 'dev', false);

        $dbm = new sfDatabaseManager($configuration);
        $db = $dbm->getDatabase('main_conn');

        //strip dsn info from doctrine to be used in pg_connect
        $search = array('pgsql:', ';');
        $dsn = str_replace($search, " ", $db->getParameter('dsn'));

        $str = $dsn.' user='.$db->getParameter('username').' password='.$db->getParameter('password');

        $conn = pg_connect($str) or die ("TaskManager CalculateBillBuildUpRateTotal --> " . pg_last_error($conn));

        try
        {
            $sql = "UPDATE ".BillBuildUpRateItemTable::getInstance()->getTableName()."
                SET total = (SELECT COALESCE(MULTIPLY(c.final_value), 0) FROM
                ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." c
                WHERE c.relation_id = ".BillBuildUpRateItemTable::getInstance()->getTableName().".id AND c.column_name <> '".BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE."' AND c.final_value <> 0 AND c.deleted_at IS NULL)
                WHERE id IN (".$this->implodedIds.") AND deleted_at IS NULL;";

            $lineTotalSql = "UPDATE ".BillBuildUpRateItemTable::getInstance()->getTableName()."
            SET line_total = CASE
               WHEN EXISTS(SELECT id FROM ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." WHERE column_name='".BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE."' AND relation_id = ".BillBuildUpRateItemTable::getInstance()->getTableName().".id AND deleted_at IS NULL)
               THEN ".BillBuildUpRateItemTable::getInstance()->getTableName().".total + (".BillBuildUpRateItemTable::getInstance()->getTableName().".total * ((SELECT final_value FROM ".BillBuildUpRateFormulatedColumnTable::getInstance()->getTableName()." WHERE column_name='".BillBuildUpRateItem::FORMULATED_COLUMN_WASTAGE."' AND relation_id = ".BillBuildUpRateItemTable::getInstance()->getTableName().".id AND deleted_at IS NULL) / 100) )
               ELSE ".BillBuildUpRateItemTable::getInstance()->getTableName().".total
            END
            WHERE ".BillBuildUpRateItemTable::getInstance()->getTableName().".id IN (".$this->implodedIds.") AND ".BillBuildUpRateItemTable::getInstance()->getTableName().".deleted_at IS NULL";

            pg_query($conn, "BEGIN WORK");

            $res = pg_query($conn, $sql);

            if (!$res)
            {
                pg_query($conn, "ROLLBACK");
            }
            else
            {
                $res = pg_query($conn, $lineTotalSql);

                if (!$res)
                {
                    pg_query($conn, "ROLLBACK");
                }
                else
                {
                    pg_query($conn, "COMMIT");
                }
            }

            pg_close($conn);
        }
        catch (Exception $e)
        {
            ob_start();//prevent output to main process
            register_shutdown_function(create_function('$pars', 'ob_end_clean();posix_kill(getmypid(), SIGKILL);'), array());//to kill self before exit();, or else the resource shared with parent will be closed

            exit(1);
        }
    }
}
?>