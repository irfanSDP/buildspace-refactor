<?php

use PCK\ContractGroupProjectUsers\ContractGroupProjectUserRepository;
use PCK\Projects\Project;
use PCK\EBiddingCommittees\EBiddingCommitteeRepository;
use PCK\EBiddings\EBiddingRepository;

class EBiddingCommitteesController  extends \BaseController {

    private $contractRepo;
    private $committeeRepo;
    private $eBiddingRepo;

    public function __construct(ContractGroupProjectUserRepository $contractRepo, EBiddingCommitteeRepository $committeeRepo, EBiddingRepository $eBiddingRepo)
    {
        $this->contractRepo = $contractRepo;
        $this->committeeRepo = $committeeRepo;
        $this->eBiddingRepo = $eBiddingRepo;
    }

    public function edit($project)
    {
        $buCompany  = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::PROJECT_OWNER))->first();
        $gcdCompany = $project->selectedCompanies()->where('contract_group_id', '=', PCK\ContractGroups\ContractGroup::getIdByGroup(PCK\ContractGroups\Types\Role::GROUP_CONTRACT))->first();

        $buCommittees          = [];
        $gcdCommittees         = [];
        $buContractGroup       = null;
        $gcdContractGroup      = null;
        $buAssignedCommittees  = [];
        $gcdAssignedCommittees = [];
        $buAssigned            = [];
        $gcdAssigned           = [];

        if($buCompany)
        {
            $buCommittees = $buCompany->getActiveUsers(true);
            $buContractGroup = $buCompany->getContractGroup($project);
            $buAssignedCommittees = $this->committeeRepo->getAssignedCommittee($project);
            $buAssigned = $this->contractRepo->getAssignedUsersByProjectAndContractGroup($project, $buContractGroup);
        }

        if($gcdCompany)
        {
            $gcdCommittees = $gcdCompany->getActiveUsers(true);
            $gcdContractGroup = $gcdCompany->getContractGroup($project);
            $gcdAssignedCommittees = $this->committeeRepo->getAssignedCommittee($project);
            $gcdAssigned = $this->contractRepo->getAssignedUsersByProjectAndContractGroup($project, $gcdContractGroup);
        }

        $eBidding = $this->eBiddingRepo->getByProjectId($project->id);
        if (! $eBidding)
        {
            Flash::error(trans('errors.anErrorOccurred'));
            return Redirect::back();
        }

        if ($eBidding->enable_zones)
        {
            $backButtonUrl = route('projects.e_bidding.zones.index', [$project->id, $eBidding->id]);
        } else {
            $backButtonUrl = route('projects.e_bidding.edit', [$project->id, $eBidding->id]);
        }

        return View::make('e_bidding_committees.edit', array(
            'project'               => $project,
            'buCompany'             => $buCompany,
            'gcdCompany'            => $gcdCompany,
            'buContractGroup'       => $buContractGroup,
            'gcdContractGroup'      => $gcdContractGroup,
            'buCommittees'          => $buCommittees,
            'gcdCommittees'         => $gcdCommittees,
            'buAssignedCommittees'  => $buAssignedCommittees,
            'gcdAssignedCommittees' => $gcdAssignedCommittees,
            'buAssigned'            => $buAssigned,
            'gcdAssigned'           => $gcdAssigned,
            'backButtonUrl'         => $backButtonUrl,
        ));
    }

    public function update(Project $project)
    {
        $user          = Confide::user();
        $inputs        = Input::all();
        
        // will batch insert new records
        $this->committeeRepo->insertByRolesEbidding($project, $inputs);

        Flash::success("Updated Committee from to E-Bidding");

        // will display flash message to remind user of successfully added user to current selected group
        return Redirect::route('projects.e_bidding.getVerifier', $project->id);
    }

}