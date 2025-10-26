<?php

use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Drivers\Cron\Scheduler;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use PCK\CronJobs\UpdateTenderTechnicalEvaluationStatusService;

class UpdateTenderStatusesCommand extends ScheduledCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'system:update-tender-statuses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Project Tender Statuses';

    private $services = [];

    public function __construct(UpdateTenderTechnicalEvaluationStatusService $updateTenderTechnicalEvaluationStatusService)
    {
        $this->services = [
            $updateTenderTechnicalEvaluationStatusService
        ];

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

        foreach($this->services as $service)
        {
            $service->handle();
        }
    }

}
