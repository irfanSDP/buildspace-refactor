<?php

use PCK\Base\Upload;
use PCK\ContractGroups\ContractGroup;
use PCK\Projects\Project;
use PCK\StructuredDocument\StructuredDocument;
use PCK\StructuredDocument\StructuredDocumentRepository;
use PCK\TenderDocumentFolders\TenderDocumentFile;
use PCK\TenderDocumentFolders\TenderDocumentFolder;
use PCK\TenderDocumentFolders\TenderDocumentFolderRepository;
use PCK\TenderDocumentFolders\TenderDocumentDownloadLog;
use PCK\Users\User;
use PCK\Companies\Company;
use PCK\ContractGroups\Types\Role;
use Carbon\Carbon;

class TenderDocumentFoldersController extends \BaseController {

    private $user;

    private $tenderDocumentFolderRepo;
    private $structuredDocumentRepository;

    public function __construct(TenderDocumentFolderRepository $tenderDocumentFolderRepo, StructuredDocumentRepository $structuredDocumentRepository)
    {
        $this->user                         = Confide::user();
        $this->tenderDocumentFolderRepo     = $tenderDocumentFolderRepo;
        $this->structuredDocumentRepository = $structuredDocumentRepository;
    }

    public function index($project)
    {
        $user = $this->user;

        $roots = TenderDocumentFolder::getRootsByProject($project);

        $folderDescendants = array();

        foreach($roots as $root)
        {
            $descendants = $root->getDescendants();

            $folderDescendants[ $root->id ] = $descendants;
        }

        return View::make('tender_document_folders.index', compact('user', 'project', 'roots', 'folderDescendants'));
    }

    public function folderCreate($project)
    {
        $inputs = Input::all();

        $isRoot = ( array_key_exists('parent_id', $inputs) && $inputs['parent_id'] ) ? false : true;

        $inputs['project_id'] = $project->id;

        $errors = array();

        try
        {
            $node = new TenderDocumentFolder();

            $node->name = $inputs['name'];

            $node->project_id = $project->id;

            $lastNode = TenderDocumentFolder::getRootsByProject($project)->last();

            if( $isRoot )
            {
                $node->priority  = ( $lastNode ) ? $lastNode->priority + 1 : 0;
                $node->root_id   = null;
                $node->parent_id = null;

                $node->save();
            }
            else
            {
                $parent = TenderDocumentFolder::find($inputs['parent_id']);

                $node->root_id     = $parent->root_id;
                $node->priority    = $parent->priority;
                $node->parent_id   = $parent->id;
                $node->folder_type = $inputs['folder_type'];
                $node->save();

                $node->makeChildOf($parent);
            }

            if( $inputs['folder_type'] == TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT )
            {
                $this->structuredDocumentRepository->create($node->id);
            }

            $success = true;
        }
        catch(Exception $e)
        {
            $errors  = $e->getErrors();
            $success = false;
        }

        return Response::json(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    public function folderUpdate($project)
    {
        $inputs               = Input::all();
        $node                 = TenderDocumentFolder::find($inputs['id']);
        $inputs['project_id'] = $project->id;
        $inputs['parent_id']  = $node->parent_id;
        $errors               = array();

        try
        {
            $node->name = $inputs['name'];

            $node->save();

            $success = true;
        }
        catch(Exception $e)
        {
            $errors  = $e->getErrors();
            $success = false;
        }

        return Response::json(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    public function folderDelete()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $inputs = Input::all();
        $node   = TenderDocumentFolder::find($inputs['id']);

        try
        {
            $this->tenderDocumentFolderRepo->sendDeleteFolderNotification($node);

            $node->delete();

            $success = true;
        }
        catch(Exception $e)
        {
            $success = false;
        }

        return Response::json(array(
            'success' => $success
        ));
    }

    public function sendNotification($project, $folderId)
    {
        $folder = $this->tenderDocumentFolderRepo->find($project, $folderId);

        $this->tenderDocumentFolderRepo->sendUploadedFileNotification($folder);

        Flash::success("Successfully send notification for Folder ({$folder->name})");

        return Redirect::back();
    }

    public function folderInfo($project, $folderId)
    {
        if( Request::ajax() )
        {
            $folder = $this->tenderDocumentFolderRepo->find($project, $folderId);

            return Response::json($folder);
        }

        App::abort(404);
    }

    public function myFolder($project, $folderId)
    {
        $node = $this->tenderDocumentFolderRepo->find($project, $folderId);

        if( $node->folder_type == TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT )
        {
            // If user has permission to edit.
            if( \PCK\Filters\TenderFilters::checkTenderAccessLevelPermissionAllowed($project, $this->user) )
            {
                return Redirect::route('structured_documents.edit', array( $project->id, $folderId, StructuredDocument::getDocument($node)->id ));
            }

            return Redirect::route('structured_documents.print', array( $project->id, $folderId, StructuredDocument::getDocument($node)->id ));
        }

        $children = $node->children()->get()->toArray();

        $user = $this->user;

        $contractGroupRecords = ContractGroup::all();

        $contractGroups = array();

        foreach($contractGroupRecords as $record)
        {
            $contractGroups[ $record->id ] = $project->getRoleName($record->group);
        }

        if( ! $node )
        {
            App::abort(404);
        }

        $canDownload = $user->hasCompanyProjectRole($project, array(
            Role::GROUP_CONTRACT,
            Role::PROJECT_OWNER,
            $project->getCallingTenderRole(),
            Role::CONTRACTOR,
        ));

        return View::make('tender_document_folders.view', compact('user', 'node', 'project', 'children', 'contractGroups', 'canDownload'));
    }

    public function upload(Project $project, $folderId)
    {
        $node = TenderDocumentFolder::find($folderId);

        if( ! $node )
        {
            App::abort(404);
        }

        $file = Input::file('file');

        $upload = new Upload;

        try
        {
            $upload->process($file);
        }
        catch(Exception $exception)
        {
            // Something went wrong. Log it.
            Log::error($exception);
            $errors = array(
                'name'  => $file->getClientOriginalName(),
                'size'  => $file->getSize(),
                'error' => $exception->getMessage()
            );

            // Return error
            return Response::json($errors, 400);
        }

        // If it now has an id, it should have been successful.
        if( $upload->id )
        {
            $fileParts = pathinfo($upload->filename);

            //save project doc file and links it to cabinet file
            $tenderDocumentFile                            = new TenderDocumentFile();
            $tenderDocumentFile->filename                  = $fileParts['filename'];
            $tenderDocumentFile->cabinet_file_id           = $upload->id;
            $tenderDocumentFile->tender_document_folder_id = $node->id;

            $tenderDocumentFile->save();

            // this creates the response structure for jquery file upload
            $success               = new stdClass();
            $success->name         = $upload->filename;
            $success->size         = $upload->size;
            $success->url          = $upload->download_url;
            $success->thumbnailUrl = $upload->generateThumbnailURL();
            $success->deleteUrl    = action('TenderDocumentFoldersController@uploadDelete', array( $project->id, $upload->id ));
            $success->deleteType   = 'POST';
            $success->fileID       = $upload->id;

            $this->tenderDocumentFolderRepo->sendUploadedFileNotification($node);

            return Response::json(array( 'files' => array( $success ) ), 200);
        }
        else
        {
            return Response::json('Error', 400);
        }
    }

    public function uploadDelete(Project $project, $id)
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $upload = Upload::find($id);
        $upload->delete();

        $success                      = new stdClass();
        $success->{$upload->filename} = true;

        return Response::json(array( 'files' => array( $success ) ), 200);

    }

    public function fileList(Project $project, $folderId)
    {
        $data = array();
        $node = TenderDocumentFolder::find($folderId);

        if( ! Request::ajax() || ! $node )
        {
            App::abort(404);
        }

        $tenderDocumentFileObj = new TenderDocumentFile();

        $files = DB::table($tenderDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.cabinet_file_id", "f.filename AS document_filename", "f.revision", "f.description", DB::raw("f.created_at AS date_issued"), "users.name AS issued_by", "uploads.filename", "uploads.extension", "uploads.path")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('f.tender_document_folder_id', '=', $node->id)
            ->whereRaw('f.id IN (SELECT f2.id FROM ' . $tenderDocumentFileObj->getTable() . ' f2  WHERE (parent_id, revision) IN
                (SELECT f3.parent_id, MAX(f3.revision) FROM ' . $tenderDocumentFileObj->getTable() . ' f3 WHERE f3.id NOT IN
                (SELECT f4.parent_id FROM ' . $tenderDocumentFileObj->getTable() . ' f4 WHERE f4.parent_id IS NOT NULL AND f4.revision <> 0 AND
                f4.tender_document_folder_id = ' . $node->id . ' GROUP BY f4.parent_id)
                GROUP BY f3.parent_id) AND f2.tender_document_folder_id = ' . $node->id . ')')
            ->orderBy('f.filename', 'asc')
            ->get();

        foreach($files as $file)
        {
            $data[] = array(
                'filename'                  => $file->document_filename . '.' . $file->extension,
                'description'               => $file->description,
                'revision'                  => $file->revision,
                'date_issued'               => $project->getProjectTimeZoneTime($file->date_issued)->format(\Config::get('dates.standard')),
                'issued_by'                 => $file->issued_by,
                'id'                        => $file->id,
                'physicalFileExists'        => TenderDocumentFile::find($file->id)->fileExists(),
                'fileDownloadLogRoute'      => route('tenderDocument.file.log.get', array($project->id, $file->id))
            );
        }

        return Response::json(array( 'data' => $data ), 200);
    }

    public function saveEntryToFileDownloadLogs($fileId, Project $project) {
        $tenderDocumentDownloadLog = new TenderDocumentDownloadLog();
        $tenderDocumentDownloadLog->tender_document_id = $fileId;
        $tenderDocumentDownloadLog->company_id = \Confide::user()->getAssignedCompany($project)->id;
        $tenderDocumentDownloadLog->user_id = \Confide::user()->id;
        $tenderDocumentDownloadLog->save();
    }

    public function getFileDownloadLogs($project, $fileId) {
        $companies = \DB::table('tender_document_download_logs')
                    ->select('company_id')
                    ->where('tender_document_id', $fileId)
                    ->distinct()
                    ->get();

        if(empty($companies)) {
            return Response::json(array('data' => array()), 200);
        }

        foreach($companies as $company) {
            $latestLog = TenderDocumentDownloadLog::where('tender_document_id', $fileId)
                                    ->where('company_id', $company->company_id)
                                    ->orderBy('created_at', 'desc')
                                    ->first();
            $comp = Company::find($company->company_id);
            $user = User::find($latestLog->user_id);
            $dateAndTime = $project->getProjectTimeZoneTime($latestLog->created_at);
            $data[] = array(
                'company'       => $comp->name,
                'user'          => $user->name,
                'dateAndTime'   => Carbon::parse($dateAndTime)->format(\Config::get('dates.full_format'))
            );
        }

        return Response::json(array('data' => $data), 200);
    }

    public function fileInfo(Project $project, $fileId)
    {
        $file         = TenderDocumentFile::with('fileProperties.createdBy')->find($fileId);
        $cabinetInfo  = $file->fileProperties;
        $thumbNailSrc = 'img/default-file.png';

        if( ! Request::ajax() || ! $file )
        {
            App::abort(404);
        }

        $thumbnail = Upload::where('filename', '=', str_replace('.' . $cabinetInfo->extension, '', $cabinetInfo->filename) . '_84x64.' . $cabinetInfo->extension)
            ->where('parent_id', '=', $cabinetInfo->id)
            ->first();

        if( $thumbnail )
        {
            $thumbNailSrc = $thumbnail->path;
        }

        return Response::json(array(
            'filename'           => $file->filename,
            'description'        => $file->description,
            'revision'           => $file->revision,
            'parent_id'          => $file->parent_id,
            'date_issued'        => date('d/m/Y', strtotime($project->getProjectTimeZoneTime($file->created_at))),
            'issued_by'          => $cabinetInfo->createdBy->name,
            'thumbnail_src'      => $thumbNailSrc,
            'file_ext'           => strtolower($cabinetInfo->extension),
            'contract_group_id'  => $file->readOnlyContractGroups->lists('id'),
            'id'                 => $file->id,
            'physicalFileExists' => $file->fileExists()
        ), 200);
    }

    public function revisionList(Project $project, $fileId)
    {
        $file = TenderDocumentFile::find($fileId);

        if( ! Request::ajax() && ! $file )
        {
            App::abort(404);
        }

        $tenderDocumentFileObj = new TenderDocumentFile();

        $files = DB::table($tenderDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.filename", "uploads.extension")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('f.id', '<>', $file->id)
            ->where('f.tender_document_folder_id', '=', $file->tender_document_folder_id)
            ->whereRaw('f.id IN (SELECT f2.id FROM ' . $tenderDocumentFileObj->getTable() . ' f2  WHERE (parent_id, revision) IN
                (SELECT f3.parent_id, MAX(f3.revision) FROM ' . $tenderDocumentFileObj->getTable() . ' f3 WHERE f3.id NOT IN
                (SELECT f4.parent_id FROM ' . $tenderDocumentFileObj->getTable() . ' f4 WHERE f4.parent_id IS NOT NULL AND f4.revision <> 0 AND
                f4.tender_document_folder_id = ' . $file->tender_document_folder_id . ' GROUP BY f4.parent_id) GROUP BY f3.parent_id)
                AND f2.tender_document_folder_id = ' . $file->tender_document_folder_id . ' AND f2.id <> ' . $file->id . ')')
            ->orderBy('f.filename')
            ->get();

        if( $file->revision > 0 )
        {
            $previousRevision = DB::table($tenderDocumentFileObj->getTable() . ' AS f')
                ->select('f.id', 'f.filename', 'uploads.extension')
                ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
                ->where('f.revision', '=', $file->revision - 1)
                ->where('f.parent_id', '=', $file->parent_id)
                ->where('f.tender_document_folder_id', '=', $file->tender_document_folder_id)
                ->first();

            array_push($files, $previousRevision);
        }
        else
        {
            $previousRevision = $file;
        }

        return Response::json(array(
            'data'     => $files,
            'selected' => $previousRevision->id
        ), 200);
    }

    public function uploadUpdate(Project $project)
    {
        $data = Input::all();

        $file = TenderDocumentFile::find($data['id']);

        if( ! ( Request::ajax() && $file ) )
        {
            App::abort(404);
        }

        $messages = array();
        $success  = true;

        // validation rules
        $rules = array(
            'filename' => 'required|max:200'
        );

        $validator = Validator::make($data, $rules);

        if( $validator->fails() )
        {
            $success  = false;
            $messages = $validator->messages();
        }
        else
        {
            $file->filename    = $data['filename'];
            $file->description = $data['description'];

            if( array_key_exists('revision_to', $data) && $data['revision_to'] > 0 && $revisedFile = TenderDocumentFile::find($data['revision_to']) )
            {
                $file->setAsNewRevisionToFile($revisedFile);
            }
            else
            {
                $file->detachFromCurrentRevision();
            }

            $file->save();

            DB::table('tender_document_files_roles_readonly')
                ->where('tender_document_file_id', '=', $file->id)
                ->delete();

            if( isset( $data['contract_group_id'] ) )
            {
                $insertRecords = array();

                foreach($data['contract_group_id'] as $contractGroupId)
                {
                    $insertRecords[] = array(
                        'tender_document_file_id' => $file->id,
                        'contract_group_id'       => $contractGroupId,
                        'created_at'              => new \DateTime(),
                        'updated_at'              => new \DateTime()
                    );
                }

                if( ! empty( $insertRecords ) )
                    DB::table('tender_document_files_roles_readonly')->insert($insertRecords);
            }
        }

        return Response::json(array(
            'success'  => $success,
            'messages' => $messages
        ), 200);
    }

    public function fileRevisions(Project $project, $fileId)
    {
        $file = TenderDocumentFile::find($fileId);

        if( ! Request::ajax() && ! $file )
        {
            App::abort(404);
        }

        return View::make('tender_document_folders.fileRevisionsView', compact('project', 'file'));
    }

    public function fileRevisionList(Project $project, $fileId)
    {
        $file = TenderDocumentFile::find($fileId);
        $data = array();

        if( ! Request::ajax() && ! $file )
        {
            App::abort(404);
        }

        $tenderDocumentFileObj = new TenderDocumentFile();

        $files = DB::table($tenderDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.cabinet_file_id", "f.filename AS document_filename", "f.revision", "f.description", "f.created_at AS date_issued", "users.name AS issued_by", "uploads.filename", "uploads.extension", "uploads.path")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->where('f.tender_document_folder_id', '=', $file->tender_document_folder_id)
            ->where('f.parent_id', '=', $file->parent_id)
            ->orderBy('f.revision')
            ->get();

        foreach($files as $file)
        {
            $data[] = array(
                'filename'    => $file->document_filename . '.' . $file->extension,
                'description' => $file->description,
                'revision'    => $file->revision,
                'date_issued' => $project->getProjectTimeZoneTime($file->date_issued)->format(\Config::get('dates.standard')),
                'issued_by'   => $file->issued_by,
                'id'          => $file->id
            );
        }

        return Response::json(array( 'data' => $data ), 200);
    }

    public function folderDownload(Project $project, $folderId)
    {
        $filesToZip = [];

        $folder = TenderDocumentFolder::find($folderId);

        $filesToZip = $this->tenderDocumentFolderRepo->getTemporaryFileAbsolutePathByDocumentFolderPath($folder);

        if( empty($filesToZip) )
        {
            Flash::error(trans('tenderDocumentFolders.noFilesToDownload'));

            return Redirect::back();
        }

        $pathToZipFile = \PCK\Helpers\Zip::zip($filesToZip);

        foreach($filesToZip as $filepath)
        {
            \PCK\Helpers\Files::deleteFile($filepath);
        }

        $zipName = "{$folder->name}.".\PCK\Helpers\Files::EXTENSION_ZIP;

        foreach($folder->descendantsAndSelf()->get() as $fol)
        {
            foreach($fol->files as $file)
            {
                $this->saveEntryToFileDownloadLogs($file->id, $project);
            }
        }

        return \PCK\Helpers\Files::download(
            $pathToZipFile,
            $zipName,
            array(
                'Content-Type: application/zip',
            )
        );
    }

    public function fileDownload(Project $project, $fileId)
    {
        $file = TenderDocumentFile::find($fileId);

        if( ! $file )
        {
            App::abort(404);
        }

        $cabinetInfo = $file->fileProperties;

        $path     = $cabinetInfo->physicalPath();
        $filename = $cabinetInfo->filename;

        $fileExt  = $cabinetInfo->extension;
        $mimeType = $cabinetInfo->mimetype;

        try
        {
            if( in_array(strtolower($cabinetInfo->extension), TenderDocumentFile::TEMPLLATE_KEYWORD_FILE_FORMATS) )
            {
                list( $path, $filename ) = $file->overwriteTenderDocumentKeyWords();
            }

            foreach($file->readOnlyContractGroups as $contractGroup)
            {
                if( $this->user->hasCompanyProjectRole($file->folder->project, $contractGroup->group) )
                {
                    list( $path, $filename, $fileExt ) = TenderDocumentFile::convertToReadOnlyFormat($filename, $file->filename, $cabinetInfo->extension, $path);

                    $mimeType = mime_content_type($path . $filename);
                }
            }

            $this->saveEntryToFileDownloadLogs($fileId, $project);
        }
        catch(Exception $e)
        {
            \Log::error("File download error. {$e->getMessage()}");
        }

        return \PCK\Helpers\Files::download(
            $path . $filename,
            $file->filename . '.' . $fileExt, array(
            'Content-Type: ' . $mimeType,
        ));
    }

    /**
     * Deletes a file by its id.
     *
     * @param Project $project
     * @param         $fileId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileDelete(Project $project, $fileId)
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $file = TenderDocumentFile::find($fileId);

        try
        {
            $file->delete();
            TenderDocumentDownloadLog::deleteDownloadLogEntryByFileId($fileId);
        }
        catch(\Symfony\Component\Filesystem\Exception\FileNotFoundException $e)
        {
            // If physical file is non-existant
            // We respond as though we just deleted it.
            return Response::json(array( 'success' => true ));
        }
        catch(Exception $e)
        {
            // Physical file cannot be deleted.
            return Response::json(array( 'success' => false, 'message' => 'File could not be deleted' ));
        }

        return Response::json(array( 'success' => true ));
    }

}