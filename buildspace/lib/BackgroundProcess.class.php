<?php

class BackgroundProcess
{
    const OS_WINDOWS = 1;
    const OS_NIX     = 2;
    const OS_OTHER   = 3;

    private $command;
    private $pid;
    protected $serverOS;

    public function __construct($command = null)
    {
        $this->command  = $command;
        $this->serverOS = $this->getOS();
    }

    /**
     * Runs the command in a background process.
     *
     * @param string $outputFile File to write the output of the process to; defaults to /dev/null
     *                           currently $outputFile has no effect when used in conjunction with a Windows server
     * @param bool $append - set to true if output should be appended to $outputfile
     */
    public function run($outputFile = '/dev/null', $append = false)
    {
        if($this->command === null)
        {
            return;
        }

        switch ($this->getOS())
        {
            case self::OS_WINDOWS:
                shell_exec(sprintf('%s &', $this->command, $outputFile));
                break;
            case self::OS_NIX:
                $this->pid = (int)shell_exec(sprintf('%s %s %s 2>&1 & echo $!', $this->command, ($append) ? '>>' : '>', $outputFile));
                break;
            default:
                throw new RuntimeException(sprintf(
                    'Could not execute command "%s" because operating system "%s" is not supported by '.
                    'BackgroundProcess.',
                    $this->command,
                    PHP_OS
                ));
        }
    }

    /**
     * Returns if the process is currently running.
     *
     * @return bool TRUE if the process is running, FALSE if not.
     */
    public function isRunning()
    {
        $this->checkSupportingOS('BackgroundProcess can only check if a process is running on *nix-based '.
                                 'systems, such as Unix, Linux or Mac OS X. You are running "%s".');
        try
        {
            $result = shell_exec(sprintf('ps %d 2>&1', $this->pid));
            if (count(preg_split("/\n/", $result)) > 2 && !preg_match('/ERROR: Process ID out of range/', $result))
            {
                return true;
            }
        }
        catch (Exception $e)
        {

        }
        return false;
    }

    /**
     * Stops the process.
     *
     * @return bool `true` if the processes was stopped, `false` otherwise.
     */
    public function stop()
    {
        $this->checkSupportingOS('BackgroundProcess can only stop a process on *nix-based systems, such as '.
                                 'Unix, Linux or Mac OS X. You are running "%s".');
        try
        {
            $result = shell_exec(sprintf('kill %d 2>&1', $this->pid));
            if (!preg_match('/No such process/', $result)) 
            {
                return true;
            }
        }
        catch (Exception $e)
        {
        }

        return false;
    }

    /**
     * Returns the ID of the process.
     *
     * @return int The ID of the process
     */
    public function getPid()
    {
        $this->checkSupportingOS('BackgroundProcess can only return the PID of a process on *nix-based systems, '.
                                 'such as Unix, Linux or Mac OS X. You are running "%s".');
        return $this->pid;
    }

    /**
     * Set the process id.
     *
     * @param $pid
     */
    protected function setPid($pid)
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    protected function getOS()
    {
        $os = strtoupper(PHP_OS);
        if (substr($os, 0, 3) === 'WIN')
        {
            return self::OS_WINDOWS;
        }
        else if ($os === 'LINUX' || $os === 'FREEBSD' || $os === 'DARWIN')
        {
            return self::OS_NIX;
        }

        return self::OS_OTHER;
    }

    /**
     * @param string $message Exception message if the OS is not supported
     *
     * @throws RuntimeException if the operating system is not supported by Cocur\BackgroundProcess
     *
     * @codeCoverageIgnore
     */
    protected function checkSupportingOS($message)
    {
        if ($this->getOS() !== self::OS_NIX)
        {
            throw new RuntimeException(sprintf($message, PHP_OS));
        }
    }

    /**
     * @param int $pid PID of process to resume
     *
     * @return BackgroundProcess\BackgroundProcess
     */
    static public function createFromPID($pid)
    {
        $process = new self();
        $process->setPid($pid);
        
        return $process;
    }
}