<?php

use Illuminate\Console\Command;
use PCK\Companies\Company;
use PCK\Users\UserRepository;

class AddCompanyAdmins extends Command {

    protected $userRepository;
    protected $emailDelimiters = ['\,','\;','\/'];
    protected $defaultUserName = 'Company Admin';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'system:add-company-admins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds company admin(s) for companies without.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->processCompanies();
    }

    public function processCompanies()
    {
        $companies = Company::doesntHave('companyAdmins')->get();

        foreach($companies as $company)
        {
            $this->addAdmins($company);
        }

        $this->info("Finished adding admins");
    }

    protected function getEmails($company)
    {
        $emailString = $company->email;

        $delimitersForPregSplit = implode('|', $this->emailDelimiters);

        $output = preg_split("/\s*({$delimitersForPregSplit})\s*/", $emailString);

        foreach($output as $key => $item)
        {
            if(!filter_var($item, FILTER_VALIDATE_EMAIL))
            {
                unset($output[$key]);

                $info = "Ignoring [{$item}] (Invalid email format). [Company:{$company->id}]";

                \Log::info($info);

                $this->info($info);
            }
        }

        return $output;
    }

    protected function addAdmins($company)
    {
        $emails = $this->getEmails($company);

        foreach($emails as $email)
        {
            $success = $this->createAccount($company, $email);

            if(!$success) $this->info("Unable to create account for {$email} (Company id:{$company->id}). Check logs for details.");
        }
    }

    protected function createAccount($company, $email)
    {
        $input = [
            'relation_column' => 'company_id',
            'company_id'      => $company->id,
            'is_admin'        => true,
            'email'           => $email,
            'name'            => $this->defaultUserName,
            'contact_number'  => $company->telephone_number,
            'username'        => $this->defaultUserName,
        ];

        $user = $this->userRepository->signUp($input);

        if( ! $user->exists or (isset($user->errors) && $user->errors->count()))
        {
            \Log::info("Failed creating admin [{$email}] (Company:{$company->id})");

            foreach($user->errors->all() as $error)
            {
                \Log::notice($error);
            }
        }

        return $user->exists;
    }

}