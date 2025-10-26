<?php

use PCK\OpenTenderBanners\OpenTenderBanners; 
use PCK\OpenTenderBanners\OpenTenderBannersRepository;
use PCK\Forms\OpenTenderBannersForm;
use PCK\Users\User;

class OpenTenderBannersController extends \BaseController {

	private $openTenderBannersForm;
	private $openTenderBannersRepository;
	
	public function __construct(OpenTenderBannersForm $openTenderBannersForm,OpenTenderBannersRepository $openTenderBannersRepository)
	{
		$this->openTenderBannersForm       = $openTenderBannersForm;
		$this->openTenderBannersRepository = $openTenderBannersRepository;
	}

	public function index()
	{
		$openTenderBanners = OpenTenderBanners::orderBy('id', 'asc')->get();

		return View::make('open_tender_banners.index', array('openTenderBanners' => $openTenderBanners)); 
	}

	public function create()
	{
		return View::make('open_tender_banners.create');
	}

	public function store()
	{
		$user = Confide::user();
		$input = Input::all();

		if (Input::hasFile('image')) {
			$file = Input::file('image');
			$originalName = $file->getClientOriginalName();
	
			try
			{
				$this->openTenderBannersForm->validate($input);
				$record                = new OpenTenderBanners;
				$record->image         = $originalName;
				$record->display_order = $input['display_order'];
				$record->start_time    = $input['start_time'];
				$record->end_time      = $input['end_time'];
				$record->created_by    = $user->id;
				$record->save();

				$this->openTenderBannersRepository->upload(Input::file('image'), $record->id , $originalName);
			}
			catch(\PCK\Exceptions\ValidationException $e)
			{
				return Redirect::to(URL::previous())
					->withErrors($e->getErrors())
					->withArrayInput($input);
			}
				
			Flash::success("Banner is added successfully!");
		}
		else
		{
			Flash::error("Failed to add banner.");	
		}

		return Redirect::to('open_tender_banners');
	}

	public function edit($id)
	{
        $openTenderBanners = OpenTenderBanners::find($id);

        return View::make('open_tender_banners.edit', array(
			'id'              => $id,
            'image'           => $openTenderBanners->image,
			'display_order'   => $openTenderBanners->display_order,
			'start_time'      => $openTenderBanners->start_time,
			'end_time'        => $openTenderBanners->end_time,
            'backRoute'       => route('open_tender_banners.index'),
        ));
	}

	public function update($id)
	{
		$user = Confide::user();
		$openTenderBanners = OpenTenderBanners::find($id);
		$input = Input::all();

		if (Input::hasFile('image')) {
			$file = Input::file('image');
			$originalName = $file->getClientOriginalName();
		}
		else
		{
			$originalName = $openTenderBanners->image;
		}

		try
		{
			$this->openTenderBannersForm->validate($input);
			$openTenderBanners->image          = $originalName;
			$openTenderBanners->display_order  = $input['display_order'];
			$openTenderBanners->start_time     = $input['start_time'];
			$openTenderBanners->end_time       = $input['end_time'];
			$openTenderBanners->created_by     = $user->id;
			$openTenderBanners->save();

			if (Input::file('image')) {
				$this->openTenderBannersRepository->upload(Input::file('image'), $openTenderBanners->id , $originalName);
			}
	
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            return Redirect::to(URL::previous())
                ->withErrors($e->getErrors())
                ->withArrayInput($input);
        }

		Flash::success("Banner is updated!");

		return Redirect::to('open_tender_banners');
	}

	public function destroy($id)
	{
		$openTenderBanners = OpenTenderBanners::find($id);

		try
		{
			$openTenderBanners->delete();
		} 
		catch(Exception $e){

			Flash::error("Banner cannot be deleted because it is used in other module.");

			return Redirect::to('open_tender_banners');
		}

		Flash::success("Banner is deleted successfully!");

		return Redirect::to('open_tender_banners');
	}


    public function dashboardNews()
    {
		$openTenderBanners = OpenTenderBanners::orderBy('id', 'asc')->get();

		return View::make('open_tender_banners.dashboard_news', array('openTenderBanners'=> $openTenderBanners,));
    }
}