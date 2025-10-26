<?php namespace PCK\EBiddings;

class EBiddingRepository
{
    public function getById($id)
    {
        return EBidding::find($id);
    }

    public function getByProjectId($projectId)
    {
        return EBidding::where('project_id', $projectId)->first();
    }

    public function create($data)
    {
        return EBidding::create($data);
    }

    public function update($recordId, $updateData)
    {
        EBidding::where('id', $recordId)->update($updateData);
        return true;
    }
}