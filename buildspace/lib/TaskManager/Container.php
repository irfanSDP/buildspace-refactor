<?php
/**
 * get_tasks()
 * * Grabs all tasks in the "tasks" directory and loads them into memory *
 * @return
 */
error_reporting(0);
function get_tasks($cwd)
{
    $tasks=array();
    $err=array();

    if ($h = opendir($cwd))
    {
        while (false !== ($file = readdir($h)))
        {
            if($file!="."&&$file!="..")
            {
                $tasks[]=$file;
            }
        }
        closedir($h);
        return $tasks;
    }
    else
    {
        $err[]=FALSE;
        $err[]="Error opening tasks directory!";
        return $err;
    }
}

$cwd=getcwd();
$taskDir = realpath(dirname(__FILE__).'/tasks');

//$cwd=$cwd."/taskmanager/tasks";
$r=get_tasks($taskDir);
if($r[0]==FALSE)
{
    echo $r[1];
}
else
{
    require_once("Manager.class.php");
    require_once("Worker.class.php");
    require_once "bootstrap.php";

    foreach($r as $task)
    {
        include($taskDir."/".$task);
    }
}
