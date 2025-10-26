<?php

use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use PCK\CronJobs\UpdateProjectTenderStatusToClosedTenderService;

class UpdateProjectCallingTenderToClosedTender extends ScheduledCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'system:update-calling-tender-to-closed-tender';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Project Tender Status with Calling Tender to Closed Tender upon deadline';

    private $service;

    /**
     * Create a new command instance.
     *
     * @param UpdateProjectTenderStatusToClosedTenderService $service
     */
    public function __construct(UpdateProjectTenderStatusToClosedTenderService $service)
    {
        $this->service = $service;

        parent::__construct();
    }

    /**
     * When a command should run
     *
     * @param Scheduler|Schedulable $scheduler
     * @return Schedulable
     */
    public function schedule(Schedulable $scheduler)
    {
        return $scheduler->everyMinutes(\Config::get('tender.MINUTES_INTERVAL'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        \Log::info("Firing scheduled command", [
            'class'   => get_class($this),
            'command' => $this->name,
        ]);

        $this->service->handle();
    }

}
