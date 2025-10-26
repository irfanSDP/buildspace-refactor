<?php namespace PCK\EBiddings;

use Carbon\Carbon;

class EBiddingZoneRepository
{
    public function getUserId()
    {
        $user = \Confide::user();
        return $user->id;
    }

    public function validHexColor($color)
    {
        if (! preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color))
        {
            return '#ffffff'; // Default color if invalid
        }
        return $color;
    }

    private function zoneData($data)
    {
        return [
            'upper_limit' => ! empty($data['upper_limit']) ? $data['upper_limit'] : 0,
            'colour'      => ! empty($data['colour']) ? $this->validHexColor($data['colour']) : '#ffffff',
            'name'        => ! empty($data['name']) ? $data['name'] : trans('eBiddingZone.defaultZoneName'),
            'description' => ! empty($data['description']) ? $data['description'] : null,
        ];
    }

    public function create($eBiddingId, $data)
    {
        $userId = $this->getUserId();

        $zoneData = $this->zoneData($data);

        $record = new EBiddingZone();
        $record->e_bidding_id   = $eBiddingId;
        $record->upper_limit    = $zoneData['upper_limit'];
        $record->colour         = $zoneData['colour'];
        $record->name           = $zoneData['name'];
        $record->description    = $zoneData['description'];
        $record->created_by     = $userId;
        $record->updated_by     = $userId;
        $record->save();

        return $record->id;
    }

    public function update($zoneId, $data)
    {
        $zoneData = $this->zoneData($data);
        $zoneData['updated_by'] = $this->getUserId();

        EBiddingZone::where('id', $zoneId)->update($zoneData);
        return true;
    }

    public function delete($zoneId)
    {
        EBiddingZone::where('id', $zoneId)->delete();
        return true;
    }

    public function clone($zoneId)
    {
        $originalZone = $this->getById($zoneId);

        if (! $originalZone) {
            return false;
        }

        return $this->create($originalZone->e_bidding_id, [
            'upper_limit'  => $originalZone->upper_limit,
            'colour'       => $originalZone->colour,
            'name'         => $originalZone->name . ' (Clone)',
            'description'  => $originalZone->description,
        ]);
    }

    public function getById($zoneId)
    {
        return EBiddingZone::find($zoneId);
    }

    public function getList($eBiddingId, $pageData)
    {
        $data = [];

        $limit = $pageData['limit'];
        $page = $pageData['page'];

        $eBidding = EBidding::find($eBiddingId);
        if (! $eBidding) {
            return [
                'data' => $data,
                'last_page' => 0,
            ];
        }

        $model = EBiddingZone::where('e_bidding_id', $eBiddingId);

        $rowCount = $model->count();
        $totalPages = ceil($rowCount / $limit);

        $records = $model->orderBy('upper_limit', 'ASC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        foreach($records as $record)
        {
            $data[] = [
                'id'          => $record->id,
                'upper_limit' => $record->upper_limit,
                'colour'      => $record->colour,
                'name'        => $record->name,
                'description' => $record->description,
                'route_update'=> route('projects.e_bidding.zones.update', [$eBidding->project_id, $eBidding->id, $record->id]),
                'route_delete'=> route('projects.e_bidding.zones.delete', [$eBidding->project_id, $eBidding->id, $record->id]),
            ];
        }

        return [
            'data' => $data,
            'last_page' => $totalPages,
        ];
    }

    public function getBidZone($eBiddingId, $amount)
    {
        $zone = EBiddingZone::where('e_bidding_id', $eBiddingId)
            ->where('upper_limit', '>=', round($amount, 0))
            ->orderBy('upper_limit', 'asc')
            ->first();

        if (! $zone)
        {
            $zone = EBiddingZone::where('e_bidding_id', $eBiddingId)->orderBy('upper_limit', 'desc')->first();
        }

        return $zone;
    }

    public function getBidZones($eBiddingId, $pageData)
    {
        $data = [];

        $limit = $pageData['limit'];
        $page = $pageData['page'];

        $eBidding = EBidding::find($eBiddingId);
        if (! $eBidding) {
            return [
                'data' => $data,
                'last_page' => 0,
            ];
        }

        $model = EBiddingZone::where('e_bidding_id', $eBiddingId);

        $rowCount = $model->count();
        $totalPages = ceil($rowCount / $limit);

        $records = $model->orderBy('upper_limit', 'ASC')
            ->skip($limit * ($page - 1))
            ->take($limit)
            ->get();

        foreach($records as $record)
        {
            $row = [];
            $row['id']          = $record->id;
            $row['colour']      = $record->colour;
            $row['name']        = $record->name;

            if (! $pageData['isBidder']) {
                $row['upper_limit'] = $record->upper_limit;
                $row['description'] = $record->description;
            }

            $data[] = $row;
        }

        return [
            'data' => $data,
            'last_page' => $totalPages,
        ];
    }
}