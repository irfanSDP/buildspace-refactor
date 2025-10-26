<?php
use Carbon\Carbon;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Intervention\Image\Facades\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use PCK\Projects\ProjectRepository;
use PCK\Projects\Project;
use PCK\DailyLabourReports\ProjectLabourRate;

use PCK\Buildspace\Project as BsProject;
use PCK\Buildspace\BillItem;
use PCK\Buildspace\ProjectMainInformation;

use PCK\Defects\DefectCategory;
use PCK\Defects\Defect;

use PCK\SiteManagement\SiteManagementUserPermission;
use PCK\SiteManagement\SiteManagementDefectRepository;
use PCK\SiteManagement\SiteManagementDefect;
use PCK\SiteManagement\SiteManagementMCAR;

use PCK\Base\Upload;
use PCK\Users\User;

use PCK\Companies\Company;

use PCK\MobileSync\MobileSync;

class MobileRestController extends \BaseController
{
    private $projectRepo;

    private $excludedModelsFromSyncUpdate = [
        'siteManagementUserPermissions'
    ];

    public function __construct(
        ProjectRepository $projectRepo
    )
    {
        $this->projectRepo = $projectRepo;
    }

    public function getUserInfo()
    {
        $user = Auth::user();

        $siteManagamentPermissions = $user->getSiteManagementUserPermissionsMobileFormat();

        return Response::json([
            'id'       => $user->id,
            'username' => $user->username,
            'name'     => $user->name,
            'email'    => $user->email,
            'siteManagementUserPermissions' => $siteManagamentPermissions
        ]);
    }

    public function getProjects()
    {
        $user = Auth::user();

        $projectIds = $this->projectRepo->getVisibleProjectIds($user);

        $projects = Project::whereIn('id', $projectIds)->with('subProjects')->orderBy('created_at', 'desc')->get();

        $projectsList = [];

        foreach($projects as $project)
        {
            $projectsList[] = array(
                'reference'           => $project->reference,
                'project_title'       => $project->title,
                'project_short_title' => $project->short_title,
                'status'              => Project::getStatusById($project->status_id),
                'subsidiary_name'     => ($project->subsidiary_id) ? $project->Subsidiary->name : "",
                'is_sub_project'      => $project->isSubProject(),
                'is_main_project'     => $project->isMainProject(),
                'contract_name'       => ($project->contract) ? trim($project->contract->name) : 'N/A',
                'state'               => ($project->state) ? trim($project->state->name) : 'N/A',
                'country'             => ($project->country) ? trim($project->country->country) : 'N/A',
                'created_at'          => Carbon::parse($project->created_at, \Config::get('app.timezone'))->toAtomString()
            );
        }

        return Response::json(['projects' => $projectsList]);
    }

    public function getProject($project)
    {
        $user = Auth::user();

        $projectInfo = [
            'reference'           => $project->reference,
            'project_title'       => $project->title,
            'project_short_title' => $project->short_title,
            'status'              => Project::getStatusById($project->status_id),
            'subsidiary_name'     => ($project->subsidiary_id) ? $project->Subsidiary->name : "",
            'is_sub_project'      => $project->isSubProject(),
            'is_main_project'     => $project->isMainProject(),
            'contract_name'       => ($project->contract) ? trim($project->contract->name) : 'N/A',
            'state'               => ($project->state) ? trim($project->state->name) : 'N/A',
            'country'             => ($project->country) ? trim($project->country->country) : 'N/A',
            'created_at'          => Carbon::parse($project->created_at, \Config::get('app.timezone'))->toAtomString(),
        ];

        return Response::json(['project' => $projectInfo]);
    }

    public function getDefects()
    {
        $user = Auth::user();
        $mobileSync = new MobileSync($user, 1234);

        $projects = $mobileSync->getProjects();
        $defects = $mobileSync->getDefects();

        $data = [];

        foreach($defects as $defect)
        {
            $data[] = $defect['data'];
        }

        return Response::json(['defects' => $data]);
    }

    public function getDefect($id)
    {
        $user = Auth::user();
        $mobileSync = new MobileSync($user, 1234);

        $defect = Defect::find($id);

        $data = [];

        if($defect)
        {
            $data = [
                'id'                 => $defect->id,
                'name'               => $defect->name,
                'defect_category_id' => $defect->defect_category_id,
                'created_at'         => Carbon::parse($defect->created_at, \Config::get('app.timezone'))->toAtomString()
            ];
        }

        return Response::json(['defect' => $data]);
    }

    public function defectAdd()
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, 1234);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            if(!array_key_exists('defect_category_id', $records))
            {
                $records['defect_category_id'] = -1;
            }

            $defectCategory = DefectCategory::findOrFail($records['defect_category_id']);

            $defect = new Defect();

            $defect->name = array_key_exists('name', $records) ? trim($records['name']) : "";
            $defect->defect_category_id = $defectCategory->id;

            $defect->save();

            $success = true;
            $msg = '';
            $httpResponseCode = 200;
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
            $httpResponseCode = 500;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ], $httpResponseCode);
    }

    public function defectUpdate($id)
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, 1234);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            if(!array_key_exists('defect_category_id', $records))
            {
                $records['defect_category_id'] = -1;
            }

            $defectCategory = DefectCategory::findOrFail($records['defect_category_id']);
            $defect = Defect::findOrFail($id);

            $defect->name = array_key_exists('name', $records) ? trim($records['name']) : "";
            $defect->defect_category_id = $defectCategory->id;

            $defect->save();

            $success = true;
            $msg = '';
            $httpResponseCode = 200;
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
            $httpResponseCode = 500;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ], $httpResponseCode);
    }

    public function defectDelete($id)
    {
        $user = Auth::user();

        if($user->email != 'admin@buildspace.com')
        {
            return Response::json([
                'error' => "Invalid credentials",
                'success' => false
            ], 500);
        }

        $mobileSync = new MobileSync($user, 1234);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            $defect = Defect::findOrFail($id);

            $defect->delete();

            $success = true;
            $msg = '';
            $httpResponseCode = 200;
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
            $httpResponseCode = 500;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ], $httpResponseCode);
    }

    public function getDefectCategories()
    {
        $user = Auth::user();
        $mobileSync = new MobileSync($user, 1234);

        $projects = $mobileSync->getProjects();
        $defectCategories = $mobileSync->getDefectCategories();

        $data = [];

        foreach($defectCategories as $defectCategory)
        {
            $data[] = $defectCategory['data'];
        }

        return Response::json(['defect_categories' => $data]);
    }

    public function getDefectCategory($id)
    {
        $user = Auth::user();
        $mobileSync = new MobileSync($user, 1234);

        $defectCategory = DefectCategory::find($id);

        $data = [];

        if($defectCategory)
        {
            $data = [
                'id'         => $defectCategory->id,
                'name'       => $defectCategory->name,
                'created_at' => Carbon::parse($defectCategory->created_at, \Config::get('app.timezone'))->toAtomString()
            ];
        }

        return Response::json(['defect_category' => $data]);
    }

    public function defectCategoryAdd()
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, 1234);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            $defectCategory = new DefectCategory();

            $defectCategory->name = array_key_exists('name', $records) ? trim($records['name']) : "";

            $defectCategory->save();

            $success = true;
            $msg = '';
            $httpResponseCode = 200;
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
            $httpResponseCode = 500;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ], $httpResponseCode);
    }

    public function defectCategoryUpdate($id)
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, 1234);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            $defectCategory = DefectCategory::findOrFail($id);

            $defectCategory->name = array_key_exists('name', $records) ? trim($records['name']) : "";

            $defectCategory->save();

            $success = true;
            $msg = '';
            $httpResponseCode = 200;
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
            $httpResponseCode = 500;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ], $httpResponseCode);
    }

    public function defectCategoryDelete($id)
    {
        $user = Auth::user();

        if($user->email != 'admin@buildspace.com')
        {
            return Response::json([
                'error' => "Invalid credentials",
                'success' => false
            ], 500);
        }

        $mobileSync = new MobileSync($user, 1234);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            $defectCategory = DefectCategory::findOrFail($id);

            $defectCategory->delete();

            $success = true;
            $msg = '';
            $httpResponseCode = 200;
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
            $httpResponseCode = 500;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ], $httpResponseCode);
    }

    public static function getBills(Project $project, $withNotListedItems=false)
    {
        $data = [];

        $query = \DB::connection('buildspace')
            ->table('bs_bill_items AS i')
            ->join('bs_bill_elements AS e', 'e.id', '=', 'i.element_id')
            ->join('bs_project_structures AS b', 'e.project_structure_id', '=', 'b.id')
            ->join('bs_project_structures AS p', 'b.root_id', '=', 'p.id')
            ->join('bs_project_main_information AS info', 'info.project_structure_id', '=', 'p.id')
            ->where('b.type', BsProject::TYPE_BILL)
            ->where('p.type', BsProject::TYPE_ROOT)
            ->where('info.eproject_origin_id', $project->id)
            ->whereNotNull('info.eproject_origin_id')
            ->whereNotIn('i.type', [BillItem::TYPE_HEADER, BillItem::TYPE_NOID, BillItem::TYPE_HEADER_N]);

        if(!$withNotListedItems)
        {
            $query->where('i.type', '<>', BillItem::TYPE_ITEM_NOT_LISTED);
        }

        $records = $query->whereNull('i.project_revision_deleted_at')
                ->whereNull('i.deleted_at')
                ->whereNull('e.deleted_at')
                ->whereNull('b.deleted_at')
                ->whereNull('p.deleted_at')
                ->whereNull('info.deleted_at')
                ->select('info.id', 'info.project_structure_id', 'info.title', 'b.id AS bill_id', 'b.title AS bill_title',
                'info.eproject_origin_id', \DB::raw('COALESCE(SUM(i.grand_total_after_markup),0) AS total'), \DB::raw('COALESCE(COUNT(i.id),0) AS no_bill_items'))
                ->groupBy('info.id', 'b.id')
                ->get();

        foreach($records as $record)
        {
            $data[] = [
                'id' => $record->bill_id,
                'project_id' => $record->eproject_origin_id,
                'title' => $record->bill_title,
                'project_title' => $record->title,
                'no_of_bill_items' => $record->no_bill_items,
                'total' => (float)$record->total
            ];
        }

        return Response::json(['bills' => $data]);
    }

    public function initialSync($deviceId)
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, $deviceId);

        $success = $mobileSync->initialSync();

        return Response::json([
            'success' => $success
        ]);
    }

    public function getQueuedRecordCount($deviceId)
    {
        try
        {
            $user = Auth::user();

            $mobileSync = new MobileSync($user, $deviceId);

            $projects = $mobileSync->getProjects();
            $defectCategories = $mobileSync->getDefectCategories();
            $defects = $mobileSync->getDefects();
            $locations = $mobileSync->getProjectStructureLocationCodes();
            $trades = $mobileSync->getTrades();
            $contractors = $mobileSync->getContractors();
            $projectLabourRates = $mobileSync->getProjectLabourRates();
            $defectCategoryTrades = $mobileSync->getDefectCategoryTrades();
            $siteManagementDefects = $mobileSync->getSiteManagementDefects();
            $siteManagementDefectAttachments = $mobileSync->getSiteManagementDefectAttachments();

            $total = count($projects)+count($defectCategories)+count($defects)+count($locations)+count($trades)+count($contractors)
            +count($projectLabourRates)+count($defectCategoryTrades)
            +count($siteManagementDefects)+count($siteManagementDefectAttachments);

            $error = null;
        }
        catch(\Exception $e)
        {
            $total = 0;
            $error = $e->getMessage();
        }

        return Response::json([
            'NumRecords' => $total,
            'error'      => $error
        ]);
    }

    public function getQueuedRecords($batchLimit, $deviceId)
    {
        $records = [];
        $batchRecords = [];
        $mobileSyncRecords = [];

        $user = Auth::user();

        $mobileSync = new MobileSync($user, $deviceId);

        $projects = $mobileSync->getProjects();
        $defectCategories = $mobileSync->getDefectCategories();
        $defects = $mobileSync->getDefects();
        $locations = $mobileSync->getProjectStructureLocationCodes();
        $trades = $mobileSync->getTrades();
        $contractors = $mobileSync->getContractors();
        $projectLabourRates = $mobileSync->getProjectLabourRates();
        $defectCategoryTrades = $mobileSync->getDefectCategoryTrades();
        $siteManagementDefects = $mobileSync->getSiteManagementDefects();
        $siteManagementDefectAttachments = $mobileSync->getSiteManagementDefectAttachments();

        $records = array_merge($projects, $defectCategories, $defects, $locations, $trades, $contractors, $projectLabourRates, $defectCategoryTrades, $siteManagementDefects, $siteManagementDefectAttachments);

        unset($projects, $defectCategories, $defects, $locations, $trades, $contractors, $projectLabourRates, $defectCategoryTrades, $siteManagementDefects, $siteManagementDefectAttachments);

        for($i=0;$i<$batchLimit;$i++)
        {
            if(array_key_exists($i, $records))
                $batchRecords[] = $records[$i];
            else
                break;
        }

        foreach($batchRecords as $record)
        {
            $mobileSyncRecords[$record['RecordType']][] = $record['SyncID'];
        }

        foreach($mobileSyncRecords as $recordType => $data)
        {
            $mobileSync->insertAcknowledgedRecords($this->translateRecordType($recordType), array_values($data));
        }

        return Response::json($batchRecords);
    }

    public function acknowledgedRecords($deviceId)
    {
        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $syncIds = json_decode($content, true);

        if(!empty($syncIds['syncIds']))
        {
            $user = Auth::user();

            $data = [];
            foreach($syncIds['syncIds'] as $syncId)
            {
                $pieces = explode("-", $syncId);
                $data[$pieces[0]][] = $pieces[1];
            }

            if(!empty($data))
            {
                $mobileSync = new MobileSync($user, $deviceId);

                foreach($data as $modelName => $records)
                {
                    $mobileSync->updateAcknowledgedRecords($this->translateRecordType($modelName), $records);
                }
            }
        }

        return Response::json(['success'=>true]);
    }

    public function syncRecords($deviceId)
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, $deviceId);

        $request = Request::instance();
        // Now we can get the content from it
        $content = $request->getContent();

        $records = json_decode($content, true);

        try
        {
            $mobileSync->syncRecords($records);

            $success = true;
            $msg = '';
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ]);
    }

    public function getAttachment($uploadId, $deviceId)
    {
        $upload = Upload::findOrFail($uploadId);
        $file = base_path().$upload->path.'/'.$upload->filename;

        $headers = array(
            'Content-Type: ' . mime_content_type( $file ),
        );

        return Response::download($file, $upload->filename, $headers);
    }

    public function upload($deviceId)
    {
        $user = Auth::user();

        $mobileSync = new MobileSync($user, $deviceId);

        $request = Request::instance();

        $data = $request->get('data');
        $attachments = $request->file('attachments');

        try
        {
            $mobileSync->syncAttachments($attachments, $data);

            $success = true;
            $msg = '';
        }
        catch(Exception $e)
        {
            $msg =  $e->getMessage();
            $success = false;
        }

        return Response::json([
            'error' => $msg,
            'success' => $success
        ]);
    }

    private function translateRecordType($recordType)
    {
        switch($recordType)
        {
            case 'projects':
                return 'projects';
            case 'defectCategories':
                return 'defect_categories';
            case 'defects':
                return 'defects';
            case 'projectStructureLocationCodes':
                return 'project_structure_location_codes';
            case 'trades':
                return 'trades';
            case 'companies':
                return 'companies';
            case 'projectLabourRates':
                return 'project_labour_rates';
            case 'defectCategoryTrades':
                return 'defect_category_trades';
            case 'siteManagementDefects':
                return 'site_management_defects';
            case 'siteManagementDefectAttachments':
                return 'uploads';
            default:
                throw new Exception('Invalid record type '.$recordType);
        }
    }
}
