<?php

use PCK\Calendars\CalendarRepository;
use PCK\Forms\AddNewWeatherRecordForm;
use PCK\WeatherRecords\WeatherRecordRepository;

class WeatherRecordsController extends \BaseController {

    private $wrRepo;

    private $calendarRepository;

    private $addForm;

    public function __construct(
        WeatherRecordRepository $wrRepo,
        CalendarRepository $calendarRepository,
        AddNewWeatherRecordForm $addForm
    )
    {
        $this->wrRepo             = $wrRepo;
        $this->calendarRepository = $calendarRepository;
        $this->addForm            = $addForm;
        $this->user               = \Confide::user();
    }

    /**
     * Display a listing of the Weather Record.
     *
     * @param $project
     *
     * @return Response
     */
    public function index($project)
    {
        $user = $this->user;
        $wrs  = $this->wrRepo->all($project);

        return View::make('weather_records.index', compact('project', 'user', 'wrs'));
    }

    /**
     * Show the form for creating a new Weather Record.
     *
     * @param      $project
     * @param null $wrId
     *
     * @return Response
     */
    public function create($project, $wrId = null)
    {
        $user          = $this->user;
        $weatherRecord = $this->wrRepo->find($wrId, true);
        $wrReportMode  = 'new';
        $events        = $this->calendarRepository->getEventsListing($project);
        $uploadedFiles = $this->getAttachmentDetails();
        $isEditor      = $user->isEditor($project);

        JavaScript::put(compact('events'));

        return View::make('weather_records.create', compact('user', 'project', 'weatherRecord', 'wrReportMode', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Store a newly created Weather Record in storage.
     *
     * @param      $project
     * @param null $wrId
     *
     * @return Response
     * @throws \Laracasts\Validation\FormValidationException
     */
    public function store($project, $wrId = null)
    {
        $user          = $this->user;
        $weatherRecord = $this->wrRepo->find($wrId, true);

        $inputs = Input::all();
        
        $this->addForm->validate($inputs);

        $inputs['date'] = $project->getAppTimeZoneTime($inputs['date'] ?? null);
        
        $wr = $this->wrRepo->add($project, $weatherRecord, $user, $inputs);

        \Flash::success("New WR ({$wr->date}) successfully added!");

        return Redirect::route('wr', array( $project->id ));
    }

    /**
     * Display the specified Weather Record.
     *
     * @param $project
     * @param $wrId
     *
     * @return Response
     */
    public function show($project, $wrId)
    {
        $user          = $this->user;
        $wr            = $this->wrRepo->find($wrId);
        $wrReportMode  = 'update';
        $events        = $this->calendarRepository->getEventsListing($project);
        $uploadedFiles = $this->getAttachmentDetails($wr);
        $isEditor      = $user->isEditor($project);

        JavaScript::put(compact('events'));

        return View::make('weather_records.show', compact('user', 'wr', 'wrReportMode', 'uploadedFiles', 'isEditor'));
    }

    /**
     * Update the specified Weather Record in storage.
     *
     * @param $project
     * @param $wrId
     *
     * @return Response
     */
    public function update($project, $wrId)
    {
        $user = $this->user;
        $wr   = $this->wrRepo->find($wrId);

        $inputs = Input::all();

        $this->addForm->validate($inputs);

        $inputs['date'] = $project->getAppTimeZoneTime($inputs['date'] ?? null);

        $wr = $this->wrRepo->update($wr, $user, $inputs);

        \Flash::success("WR ({$wr->date}) successfully updated!");

        return Redirect::route('wr', array( $project->id ));
    }

    /**
     * Update the specified Engineer Instruction's record to attach AI in storage.
     *
     * @param $project
     * @param $wrId
     *
     * @return Response
     */
    public function architectUpdate($project, $wrId)
    {
        $user = $this->user;
        $wr   = $this->wrRepo->updateArchitectVerificationStatus($this->wrRepo->find($wrId), $user);

        \Flash::success("WR ({$wr->date}) successfully updated!");

        return Redirect::route('wr', array( $project->id ));
    }

    /**
     * Remove the specified Weather Record from storage.
     *
     * @param $project
     * @param $wrId
     *
     * @return Response
     */
    public function destroy($project, $wrId)
    {
        $wr = $this->wrRepo->find($wrId);

        $this->wrRepo->delete($wr);

        \Flash::success("WR ({$wr->date}) successfully deleted!");

        return Redirect::route('wr', array( $project->id ));
    }

}