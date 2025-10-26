<?php

use Illuminate\Console\Command;

class UpdateLanguageList extends Command {

    protected $name = 'system:update-language-list';

    protected $description = 'Updates the list of languages in the database';

    private $service;

    public function __construct(\PCK\Commands\UpdateLanguageListService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    public function fire()
    {
        $this->service->handle();

        $this->output->write('List of languages is up to date.');
    }

}
