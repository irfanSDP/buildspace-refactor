<?php

use PCK\Base\Upload;
use PCK\Helpers\Hierarchy\AdjacencyListsAndNestedSets;
use PCK\StructuredDocument\StructuredDocument;
use PCK\StructuredDocument\StructuredDocumentRepository;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFile;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder;
use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolderRepository;
use PCK\TenderDocumentFolders\TenderDocumentFolder;
use PCK\WorkCategories\WorkCategory;

class TemplateTenderDocumentFoldersController extends \BaseController {

    private $user;

    private $templateTenderDocumentFolderRepository;
    private $structuredDocumentRepository;

    public function __construct(TemplateTenderDocumentFolderRepository $templateTenderDocumentFolderRepository, StructuredDocumentRepository $structuredDocumentRepository)
    {
        $this->user                                   = Confide::user();
        $this->templateTenderDocumentFolderRepository = $templateTenderDocumentFolderRepository;
        $this->structuredDocumentRepository           = $structuredDocumentRepository;
    }

    public function directory()
    {
        $rootFolders = TemplateTenderDocumentFolder::getAllRootFolders()->sortBy('serial_number');

        return View::make('tender_document_folders.template.directory', array(
            'rootFolders' => $rootFolders,
        ));
    }

    public function index(int $rootId)
    {
        $root = TemplateTenderDocumentFolder::getRootFolder($rootId);

        if(!$root)
        {
            return Redirect::route('tender_documents.template.directory');
        }

        $converter = new AdjacencyListsAndNestedSets();

        $nestedSetArray = $this->templateTenderDocumentFolderRepository->getNestedSetArray($root->getDescendants());
        $converter->setNestedSet($root->id, $nestedSetArray);
        $descendants = $converter->convertNestedSetToAdjacencyList();

        $workCategories = $root->getAvailableWorkCategories();

        return View::make('tender_document_folders.template.index', array(
            'descendants'         => $descendants,
            'rootName'            => $root->name,
            'root'                => $root,
            'workCategories'      => $workCategories,
            'bqFolderDefaultName' => TenderDocumentFolder::DEFAULT_BQ_FILES_FOLDER_NAME,
        ));
    }

    public function rename()
    {
        $inputs = Input::all();
        $node   = TemplateTenderDocumentFolder::find($inputs['id']);
        $errors = array();

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

        return Response::json(compact('success', 'errors'));
    }

    public function folderCreate(int $rootId)
    {
        $inputs = Input::all();

        $parent = $inputs['parent_id'] ? TemplateTenderDocumentFolder::find($inputs['parent_id']) : TemplateTenderDocumentFolder::getRootFolder($rootId);

        $errors = array();
        try
        {
            if(!$parent)
            {
                throw new Exception('Parent folder cannot be null');
            }

            $node = new TemplateTenderDocumentFolder();

            $node->name        = $inputs['name'];
            $node->parent_id   = $parent->id;
            $node->root_id     = $parent->root_id;
            $node->depth       = $parent->depth + 1;
            $node->folder_type = $inputs['folder_type'];

            $node->save();

            $node->makeChildOf($parent);

            if( $inputs['folder_type'] == TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT )
            {
                $this->structuredDocumentRepository->createTemplate($node->id);
            }

            $success = true;
        }
        catch(Exception $e)
        {
            $errors  = $e->getMessage();
            $success = false;
        }

        return Response::json(array(
            'success' => $success,
            'errors'  => $errors
        ));
    }

    public function delete()
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $inputs = Input::all();
        $node   = TemplateTenderDocumentFolder::find($inputs['id']);

        try
        {
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

    public function getFolderInfo()
    {
        if( Request::ajax() )
        {
            $folderId = Request::get('id');
            $folder   = TemplateTenderDocumentFolder::find($folderId);

            return Response::json($folder);
        }

        App::abort(404);
    }

    /**
     * Saves the new position (hierarchy) of the folders.
     *
     * Todo: Consider locking related folder records before this process starts so that an added folder somewhere within the set will not corrupt the data.
     *
     * @param int $rootId
     *
     * @return string
     */
    public function saveNewFolderPosition(int $rootId)
    {
        $foldersJson = Input::get('folders');

        $success = $this->templateTenderDocumentFolderRepository->repositionFolders($foldersJson, $rootId);

        return json_encode(array( 'success' => $success ));
    }

    public function show($folderId)
    {
        $node           = $this->templateTenderDocumentFolderRepository->find($folderId);
        $workCategories = WorkCategory::orderBy('name')->lists('name', 'id');

        if( $node->folder_type == TenderDocumentFolder::TYPE_STRUCTURED_DOCUMENT )
        {
            return Redirect::route('structured_documents.template.edit', array( $folderId, StructuredDocument::getDocument($node)->id ));
        }

        $workCategories = array( 0 => trans('documentManagementFolders.all') ) + $workCategories;

        $contractGroupRecords = \PCK\ContractGroups\ContractGroup::all();

        $contractGroups = array();

        foreach($contractGroupRecords as $record)
        {
            $contractGroups[ $record->id ] = $record->name;
        }

        $children = $node->children()->get()->toArray();

        $user = $this->user;

        $folderRoute = 'tender_documents.template.show';

        if( ! $node )
        {
            App::abort(404);
        }

        return View::make('tender_document_folders.template.view', compact('user', 'workCategories', 'contractGroups', 'node', 'project', 'children', 'folderRoute'));
    }

    public function upload($folderId)
    {
        $node = TemplateTenderDocumentFolder::find($folderId);

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
            $tenderDocumentFile                  = new TemplateTenderDocumentFile();
            $tenderDocumentFile->filename        = $fileParts['filename'];
            $tenderDocumentFile->cabinet_file_id = $upload->id;
            $tenderDocumentFile->folder_id       = $node->id;

            $tenderDocumentFile->save();

            // this creates the response structure for jquery file upload
            $success               = new stdClass();
            $success->name         = $upload->filename;
            $success->size         = $upload->size;
            $success->url          = $upload->download_url;
            $success->thumbnailUrl = $upload->generateThumbnailURL();
            $success->deleteUrl    = action('TemplateTenderDocumentFoldersController@uploadDelete', $upload->id);
            $success->deleteType   = 'POST';
            $success->fileID       = $upload->id;

            return Response::json(array( 'files' => array( $success ) ), 200);
        }
        else
        {
            return Response::json('Error', 400);
        }
    }

//
    public function uploadDelete($id)
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

//
    public function fileList($folderId)
    {
        $data = array();
        $node = TemplateTenderDocumentFolder::find($folderId);

        if( ! Request::ajax() || ! $node )
        {
            App::abort(404);
        }

        $tenderDocumentFileObj = new TemplateTenderDocumentFile();

        $files = DB::table($tenderDocumentFileObj->getTable() . ' AS f')
            ->select("f.id", "f.cabinet_file_id", "f.filename AS document_filename", "f.work_category_id", "work_categories.name AS work_category_name", "f.description", DB::raw(" to_char(f.created_at, 'DD/MM/YYYY') AS date_issued"), "users.name AS issued_by", "uploads.filename", "uploads.extension", "uploads.path")
            ->join('uploads', 'f.cabinet_file_id', '=', 'uploads.id')
            ->join('users', 'uploads.user_id', '=', 'users.id')
            ->leftJoin('work_categories', 'f.work_category_id', '=', 'work_categories.id')
            ->where('f.folder_id', '=', $node->id)
            ->orderBy('f.filename', 'asc')
            ->get();

        foreach($files as $file)
        {
            $data[] = array(
                'filename'           => $file->document_filename . '.' . $file->extension,
                'work_category_id'   => ! empty( $file->work_category_id ) ? $file->work_category_id : 0,
                'work_category_name' => ! empty( $file->work_category_id ) ? $file->work_category_name : trans('documentManagementFolders.all'),
                'description'        => $file->description,
                'date_issued'        => $file->date_issued,
                'issued_by'          => $file->issued_by,
                'id'                 => $file->id,
                'physicalFileExists' => TemplateTenderDocumentFile::find($file->id)->fileExists()
            );
        }

        return Response::json(array( 'data' => $data ), 200);
    }

    public function fileInfo($fileId)
    {
        $file         = TemplateTenderDocumentFile::with('fileProperties.createdBy')->find($fileId);
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
            'work_category_id'   => ! empty( $file->work_category_id ) ? $file->work_category_id : 0,
            'description'        => $file->description,
            'parent_id'          => $file->parent_id,
            'date_issued'        => date('d/m/Y', strtotime($file->created_at)),
            'issued_by'          => $cabinetInfo->createdBy->name,
            'thumbnail_src'      => $thumbNailSrc,
            'file_ext'           => $cabinetInfo->extension,
            'contract_group_id'  => $file->readOnlyContractGroups->lists('id'),
            'id'                 => $file->id,
            'physicalFileExists' => $file->fileExists()
        ), 200);
    }

    public function uploadUpdate()
    {
        $data = Input::all();

        $file = TemplateTenderDocumentFile::find($data['id']);

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
            $file->filename         = $data['filename'];
            $file->description      = $data['description'];
            $file->work_category_id = $data['work_category_id'] != 0 ? $data['work_category_id'] : null;

            $file->save();

            DB::table('template_tender_document_files_roles_readonly')
                ->where('template_tender_document_file_id', '=', $file->id)
                ->delete();

            if( isset( $data['contract_group_id'] ) )
            {
                $insertRecords = array();

                foreach($data['contract_group_id'] as $contractGroupId)
                {
                    $insertRecords[] = array(
                        'template_tender_document_file_id' => $file->id,
                        'contract_group_id'                => $contractGroupId,
                        'created_at'                       => new \DateTime(),
                        'updated_at'                       => new \DateTime()
                    );
                }

                if( ! empty( $insertRecords ) )
                    DB::table('template_tender_document_files_roles_readonly')->insert($insertRecords);
            }
        }

        return Response::json(array(
            'success'  => $success,
            'messages' => $messages
        ), 200);
    }

    public function fileDownload($fileId)
    {
        $file        = TemplateTenderDocumentFile::find($fileId);
        $cabinetInfo = $file->fileProperties;

        if( ! $file )
        {
            App::abort(404);
        }

        return \PCK\Helpers\Files::download(
            $cabinetInfo->physicalPath() . $cabinetInfo->filename,
            $file->filename . '.' . $cabinetInfo->extension, array(
            'Content-Type: ' . $cabinetInfo->mimetype,
        ));
    }
//
    /**
     * Deletes a file by its id.
     *
     * @param $fileId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fileDelete($fileId)
    {
        if( ! Request::ajax() )
        {
            App::abort(404);
        }

        $file = TemplateTenderDocumentFile::find($fileId);

        try
        {
            $file->delete();
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

    public function assignWorkCategory(int $rootId)
    {
        $this->templateTenderDocumentFolderRepository->syncWorkCategories($rootId, Input::get('work_category_id') ?? array());

        Flash::success(trans('tenderDocumentFolders.registeredWorkCategories'));

        return Redirect::back();
    }

    public function createNewSet()
    {
        return Redirect::route('tender_documents.template.index', array( $this->templateTenderDocumentFolderRepository->createNewSet()->id ));
    }

    public function deleteSet(int $rootId)
    {
        $this->templateTenderDocumentFolderRepository->deleteSet($rootId);

        Flash::success(trans('tenderDocumentFolders.templateDeleted') . '. ' . trans('tenderDocumentFolders.serialNumbersReset'));

        return Redirect::back();
    }

}