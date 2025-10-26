<?php

use PCK\OpenTenderNews\OpenTenderNews; 
use PCK\Forms\OpenTenderNewsForm;
use PCK\Users\User;

class OpenTenderNewsController extends \BaseController {

	private $openTenderNewsForm;
	
	public function __construct(OpenTenderNewsForm $openTenderNewsForm)
	{
		$this->openTenderNewsForm = $openTenderNewsForm;
	}

	public function index()
	{
		$openTenderNews = OpenTenderNews::orderBy('id', 'asc')->get();

		return View::make('open_tender_news.index', array('openTenderNews' => $openTenderNews)); 
	}

	public function create()
	{
		return View::make('open_tender_news.create');
	}

	public function store()
	{
		$user = Confide::user();
		$input = Input::all();
		try
		{
			$this->openTenderNewsForm->validate($input);
			$record                = new OpenTenderNews;
			$record->description   = $input['description'];
			$record->status        = $input['status'];
			$record->subsidiary_id = $input['department'];
			$record->start_time    = $input['start_time'];
			$record->end_time      = $input['end_time'];
			$record->created_by    = $user->id;
			$record->save();

		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }
			
		Flash::success("News is added successfully!");

		return Redirect::to('open_tender_news');
	}

	public function edit($id)
	{
        $openTenderNews = OpenTenderNews::find($id);

        return View::make('open_tender_news.edit', array(
			'id'              => $id,
            'description'     => $openTenderNews->description,
			'status'          => $openTenderNews->status,
			'department'      => $openTenderNews->subsidiary_id,
			'start_time'      => $openTenderNews->start_time,
			'end_time'        => $openTenderNews->end_time,
            'backRoute'       => route('open_tender_news.index'),
        ));
	}

	public function update($id)
	{
		$user = Confide::user();
		$openTenderNews = OpenTenderNews::find($id);
		$input = Input::all();

		try
		{
			$this->openTenderNewsForm->validate($input);
			$openTenderNews->description    = $input['description'];
			$openTenderNews->status         = $input['status'];
			$openTenderNews->subsidiary_id  = $input['department'];
			$openTenderNews->start_time     = $input['start_time'];
			$openTenderNews->end_time       = $input['end_time'];
			$openTenderNews->created_by     = $user->id;
			$openTenderNews->save();
	
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("News is updated!");

		return Redirect::to('open_tender_news');
	}

	public function destroy($id)
	{
		$openTenderNews = OpenTenderNews::find($id);

		try
		{
			$openTenderNews->delete();
		} 
		catch(Exception $e){

			Flash::error("News cannot be deleted because it is used in other module.");

			return Redirect::to('open_tender_news');
		}

		Flash::success("News is deleted successfully!");

		return Redirect::to('open_tender_news');
	}


    public function dashboardNews()
    {
		$openTenderNews = OpenTenderNews::orderBy('id', 'asc')->get();

		return View::make('open_tender_news.dashboard_news', array('openTenderNews'=> $openTenderNews,));
    }
}