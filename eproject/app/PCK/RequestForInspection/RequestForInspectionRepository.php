<?php namespace PCK\RequestForInspection;

use PCK\Base\BaseModuleRepository;
use PCK\DirectedTo\DirectedTo;
use PCK\Projects\Project;
use PCK\Verifier\Verifier;

class RequestForInspectionRepository extends BaseModuleRepository {

    /**
     * Find by Request by id.
     *
     * @param $id
     *
     * @return RequestForInspection
     */
    public function find($id)
    {
        return RequestForInspection::find($id);
    }

    /**
     * Find Inspection by id.
     *
     * @param $id
     *
     * @return RequestForInspectionInspection
     */
    public function findInspection($id)
    {
        return RequestForInspectionInspection::find($id);
    }

    /**
     * Find Reply by id.
     *
     * @param $id
     *
     * @return RequestForInspectionReply
     */
    public function findReply($id)
    {
        return RequestForInspectionReply::find($id);
    }

    /**
     * Issues a new Request for Inspection.
     *
     * @param Project $project
     * @param array   $respondents
     * @param         $input
     *
     * @return RequestForInspection
     */
    public function issueNew(Project $project, array $respondents, $input)
    {
        $currentUser = \Confide::user();

        $input['reference_number'] = $input['reference_number'] ?? RequestForInspection::getNextReferenceNumber($project);
        $input['created_by']       = $currentUser->id;
        $input['project_id']       = $project->id;
        $input['status']           = RequestForInspection::STATUS_REQUESTING;
        $requestForInspection      = new RequestForInspection($input);
        $requestForInspection->save();

        $this->saveAttachments($requestForInspection, $input);

        DirectedTo::directMultipleTo($respondents, $requestForInspection);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $requestForInspection);

        return $requestForInspection;
    }

    public function updateRequest(RequestForInspection $request, array $respondents, $input)
    {
        $request->update($input);

        $this->saveAttachments($request, $input);

        DirectedTo::directMultipleTo($respondents, $request);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $request);

        return $request;
    }

    public function addInspectionRecord($requestId, $input)
    {
        $request = RequestForInspection::find($requestId);

        $inspection = new RequestForInspectionInspection($input);

        $inspection->request_id      = $request->id;
        $inspection->created_by      = \Confide::user()->id;
        $inspection->sequence_number = $request->getNextSequenceNumber();

        $inspection->save();

        $this->saveAttachments($inspection, $input);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $inspection);

        return $inspection;
    }

    public function updateInspectionRecord(RequestForInspectionInspection $inspection, $input)
    {
        // Can only update the very last inspection.
        if( $inspection->id != $inspection->request->inspections->last()->id ) return false;

        $inspection->update($input);

        $this->saveAttachments($inspection, $input);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $inspection);

        return $inspection;
    }

    public function replyInspection($requestId, $inspectionId, array $respondents, $input)
    {
        $reply = new RequestForInspectionReply($input);

        $reply->request_id    = $requestId;
        $reply->inspection_id = $inspectionId;
        $reply->created_by    = \Confide::user()->id;

        $reply->save();

        $this->saveAttachments($reply, $input);

        DirectedTo::directMultipleTo($respondents, $reply);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $reply);

        return $reply;
    }

    public function updateInspectionReply(RequestForInspectionReply $reply, array $respondents, $input)
    {
        // Can only update the reply of the very last inspection.
        if( $reply->id != $reply->inspection->request->inspections->last()->reply->id ) return false;

        $reply->update($input);

        $this->saveAttachments($reply, $input);

        DirectedTo::directMultipleTo($respondents, $reply);

        Verifier::setVerifiers($input['verifiers'] ?? array(), $reply);

        return $reply;
    }

}