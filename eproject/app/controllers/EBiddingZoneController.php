<?php

use PCK\EBiddings\EBiddingRepository;
use PCK\EBiddings\EBiddingZoneRepository;
use PCK\Projects\Project;

class EBiddingZoneController extends \BaseController {

    protected $eBiddingRepo;
    protected $eBiddingZoneRepo;

	public function __construct(
        EBiddingRepository $eBiddingRepo,
        EBiddingZoneRepository $eBiddingZoneRepo
    ) {
        $this->eBiddingRepo = $eBiddingRepo;
        $this->eBiddingZoneRepo = $eBiddingZoneRepo;
	}

	public function index(Project $project, $eBiddingId)
    {
        $eBidding = $this->eBiddingRepo->getById($eBiddingId);
        if (! $eBidding) {
            return Redirect::route('projects.e_bidding.index', [$project->id]);
        }

        $currencyCode = $project->modified_currency_code; // Currency code

        return View::make('e_bidding.setup.zones.index', [
            'project' => $project,
            'eBidding' => $eBidding,
            'currencyCode' => $currencyCode,
        ]);
    }

    public function getList(Project $project, $eBiddingId)
    {
        $request = Request::instance();

        $limit = ((int)$request->get('size')) ? (int)$request->get('size') : 1;
        $page = ((int)$request->get('page')) ? ((int)$request->get('page')) : 1;

        $data = $this->eBiddingZoneRepo->getList($eBiddingId, [
            'page' => $page,
            'limit' => $limit,
        ]);

        return Response::json($data);
    }

	public function create(Project $project, $eBiddingId)
	{
        $eBidding   = $this->eBiddingRepo->getById($eBiddingId);
        $currencyCode = $project->modified_currency_code;   // Currency code

        return View::make('open_tenders.e_biddings.zones.create', array(
            'project' => $project,
            'eBidding' => $eBidding,
            'currencyCode' => $currencyCode,
        ));
	}

	public function store(Project $project, $eBiddingId)
	{
        $result = [
            'success' => false,
            'message' => null,
        ];

        $input = Input::all();

        $eBidding = $this->eBiddingRepo->getById($eBiddingId);
        if (! $eBidding) {
            $result['message'] = trans('errors.anErrorOccurred');
            return Response::json($result);
        }

        try {
            $zoneId = $this->eBiddingZoneRepo->create($eBidding->id, $input);
        } catch(\PCK\Exceptions\ValidationException $e) {
            $zoneId = null;
        }

        if (empty($zoneId)) {
            $result['message'] = trans('errors.anErrorOccurred');
        } else {
            $result['success'] = true;
        }

        return Response::json($result);
	}

	public function edit(Project $project, $eBiddingId, $zoneId)
	{
        $result = [
            'success' => false,
            'data' => null,
            'message' => null,
        ];

        $record = $this->eBiddingZoneRepo->getById($zoneId);

        if (! $record) {
            $result['message'] = trans('errors.anErrorOccurred');
            return Response::json($result);
        }

        $result['success'] = true;
        $result['message'] = trans('eBiddingZone.zoneCreated');
        $result['data'] = [
            'id'          => $record->id,
            'upper_limit' => $record->upper_limit,
            'colour'      => $record->colour,
            'name'        => $record->name,
            'description' => $record->description,
            'route_update'=> route('projects.e_bidding.zones.update', [$project->id, $eBiddingId, $record->id]),
            'route_delete'=> route('projects.e_bidding.zones.delete', [$project->id, $eBiddingId, $record->id]),
        ];

        return Response::json($result);
	}

	public function update(Project $project, $eBiddingId, $zoneId)
	{
        $result = [
            'success' => false,
            'message' => null,
        ];
		$input = Input::all();

		try
		{
            $record = $this->eBiddingZoneRepo->getById($zoneId);
            if (! $record) {
                $result['message'] = trans('errors.anErrorOccurred');
                return Response::json($result);
            }

            $success = $this->eBiddingZoneRepo->update($zoneId, [
                'upper_limit' => $input['upper_limit'],
                'colour'      => $input['colour'] ?? null,
                'name'        => $input['name'],
                'description' => $input['description'] ?? null,
            ]);
		}
		catch(\PCK\Exceptions\ValidationException $e)
		{
            $success = false;
        }

        if ($success) {
            $result['success'] = true;
            $result['message'] = trans('eBiddingZone.zoneUpdated');
        } else {
            $result['message'] = trans('errors.anErrorOccurred');
        }

        return Response::json($result);
	}

    public function clone(Project $project, $eBiddingId, $zoneId)
    {
        $result = [
            'success' => false,
            'message' => null,
        ];

        try
        {
            $record = $this->eBiddingZoneRepo->getById($zoneId);
            if (! $record) {
                $result['message'] = trans('errors.anErrorOccurred');
                return Response::json($result);
            }

            $success = $this->eBiddingZoneRepo->clone($record->id);
        }
        catch(\PCK\Exceptions\ValidationException $e)
        {
            $success = false;
        }

        if ($success) {
            $result['success'] = true;
            $result['message'] = trans('eBiddingZone.zoneCreated');
        } else {
            $result['message'] = trans('errors.anErrorOccurred');
        }

        return Response::json($result);
    }

    public function destroy(Project $project, $eBiddingId, $zoneId)
    {
        $result = [
            'success' => false,
            'message' => null,
        ];

        $record = $this->eBiddingZoneRepo->getById($zoneId);
        if (! $record) {
            $result['message'] = trans('errors.anErrorOccurred');
            return Response::json($result);
        }

        try {
            $success = $this->eBiddingZoneRepo->delete($zoneId);
        } catch(\PCK\Exceptions\ValidationException $e) {
            $success = false;
        }

        if ($success) {
            $result['success'] = true;
            $result['message'] = trans('eBiddingZone.zoneDeleted');
        } else {
            $result['message'] = trans('errors.anErrorOccurred');
        }

        return Response::json($result);
    }
}