<?php

use PCK\Users\User;
use PCK\Folder\FileNode;
use PCK\ContractGroupCategory\ContractGroupCategory;

class FileNodesTest extends RollbackTestCase {

    protected function constructTree()
    {
        \DB::statement("truncate file_nodes restart identity cascade");

        $user = User::select('users.*')
            ->leftJoin('companies', 'companies.id', '=', 'users.company_id')
            ->leftJoin('contract_group_categories', 'contract_group_categories.id', '=', 'companies.contract_group_category_id')
            ->where(function($query){
                $query->where('contract_group_categories.type', '!=', ContractGroupCategory::TYPE_EXTERNAL);
                $query->orWhereNull('contract_group_categories.type');
            })
            ->first();

        $this->be($user);

        /*
        id  | root_id   | parent_id | lft   | rgt   | depth | priority  | name
        1   | 1         |           | 1     | 2     | 0     | 1         | F1

        2   | 2         |           | 1     | 14    | 0     | 2         | F2
        4   | 2         | 2         | 2     | 3     | 1     | 1         | F2.1
        5   | 2         | 2         | 4     | 11    | 1     | 2         | F2.2
        7   | 2         | 5         | 5     | 6     | 2     | 1         | F2.2.1
        8   | 2         | 5         | 7     | 8     | 2     | 2         | F2.2.2
        9   | 2         | 5         | 9     | 10    | 2     | 3         | F2.2.3
        6   | 2         | 2         | 12    | 13    | 1     | 3         | F2.3

        3   | 3         |           | 1     | 14    | 0     | 2         | F3
        10  | 3         | 3         | 2     | 3     | 1     | 1         | F3.1
        11  | 3         | 3         | 4     | 11    | 1     | 2         | F3.2
        13  | 3         | 11        | 5     | 6     | 2     | 1         | F3.2.1
        14  | 3         | 11        | 7     | 8     | 2     | 2         | F3.2.2
        15  | 3         | 11        | 9     | 10    | 2     | 3         | F3.2.3
        12  | 3         | 3         | 12    | 13    | 1     | 3         | F3.3

        */

        $formData = [
            'file_node_id' => -1,
            'name' => 'F1',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 0), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 0), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 0), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2.1',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 2), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2.2',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 2), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2.3',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 2), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2.2.1',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 5), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2.2.2',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 5), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F2.2.3',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 5), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3.1',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 3), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3.2',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 3), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3.3',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 3), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3.2.1',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 11), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3.2.2',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 11), $formData);

        $formData = [
            'file_node_id' => -1,
            'name' => 'F3.2.3',
        ];
        $response = $this->action('POST', 'FoldersController@storeOrUpdate', array('folderId' => 11), $formData);
    }

    protected function validateInitialNodes()
    {
        $this->validateNodeData('F1', ['id' => 1, 'root_id' => 1, 'parent_id' => null, 'lft' => 1, 'rgt' => 2, 'depth' => 0, 'priority' => 1]);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 2, 'parent_id' => null, 'lft' => 1, 'rgt' => 14, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 2, 'parent_id' => 2, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 2, 'parent_id' => 2, 'lft' => 4, 'rgt' => 11, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 2, 'parent_id' => 5, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 2, 'parent_id' => 5, 'lft' => 7, 'rgt' => 8, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 2, 'parent_id' => 5, 'lft' => 9, 'rgt' => 10, 'depth' => 2, 'priority' => 3]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 2, 'parent_id' => 2, 'lft' => 12, 'rgt' => 13, 'depth' => 1, 'priority' => 3]);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 14, 'depth' => 0, 'priority' => 3]);
        $this->validateNodeData('F3.1', ['id' => 10, 'root_id' => 3, 'parent_id' => 3, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F3.2', ['id' => 11, 'root_id' => 3, 'parent_id' => 3, 'lft' => 4, 'rgt' => 11, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F3.2.1', ['id' => 13, 'root_id' => 3, 'parent_id' => 11, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F3.2.2', ['id' => 14, 'root_id' => 3, 'parent_id' => 11, 'lft' => 7, 'rgt' => 8, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F3.2.3', ['id' => 15, 'root_id' => 3, 'parent_id' => 11, 'lft' => 9, 'rgt' => 10, 'depth' => 2, 'priority' => 3]);
        $this->validateNodeData('F3.3', ['id' => 12, 'root_id' => 3, 'parent_id' => 3, 'lft' => 12, 'rgt' => 13, 'depth' => 1, 'priority' => 3]);
    }

    protected function validateNodeData($name, $data)
    {
        $node = FileNode::where('name', '=', $name)->first();

        $success = true;

        foreach($data as $key => $attribute)
        {
            if($node->{$key} !== $attribute)
            {
                $success = false;
                print_r($node->toArray());
                print_r(PHP_EOL);
                print_r("Key: {$key}");
                print_r(PHP_EOL);
                print_r("Expected Value: {$attribute}");
                print_r(PHP_EOL);
                break;
            }
        }

        self::assertEquals(true, $success);
    }

    public function testTreeCreation()
    {
        $this->constructTree();

        $this->validateInitialNodes();
    }

    public function testMoveRootToGlobal()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 0,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 2), $formData);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 2, 'parent_id' => null, 'lft' => 1, 'rgt' => 14, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 14, 'depth' => 0, 'priority' => 3]);
    }

    public function testMoveRootToRoot()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 3,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 2), $formData);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 3, 'parent_id' => 3, 'lft' => 14, 'rgt' => 27, 'depth' => 1, 'priority' => 4]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 3, 'parent_id' => 2, 'lft' => 15, 'rgt' => 16, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 3, 'parent_id' => 2, 'lft' => 17, 'rgt' => 24, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 3, 'parent_id' => 5, 'lft' => 18, 'rgt' => 19, 'depth' => 3, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 3, 'parent_id' => 5, 'lft' => 20, 'rgt' => 21, 'depth' => 3, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 3, 'parent_id' => 5, 'lft' => 22, 'rgt' => 23, 'depth' => 3, 'priority' => 3]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 3, 'parent_id' => 2, 'lft' => 25, 'rgt' => 26, 'depth' => 2, 'priority' => 3]);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 28, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F3.1', ['id' => 10, 'root_id' => 3, 'parent_id' => 3, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F3.2', ['id' => 11, 'root_id' => 3, 'parent_id' => 3, 'lft' => 4, 'rgt' => 11, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F3.2.1', ['id' => 13, 'root_id' => 3, 'parent_id' => 11, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F3.2.2', ['id' => 14, 'root_id' => 3, 'parent_id' => 11, 'lft' => 7, 'rgt' => 8, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F3.2.3', ['id' => 15, 'root_id' => 3, 'parent_id' => 11, 'lft' => 9, 'rgt' => 10, 'depth' => 2, 'priority' => 3]);
        $this->validateNodeData('F3.3', ['id' => 12, 'root_id' => 3, 'parent_id' => 3, 'lft' => 12, 'rgt' => 13, 'depth' => 1, 'priority' => 3]);
    }

    public function testMoveRootToDepth1DifferentScopeSubFolder()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 11,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 2), $formData);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 28, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F3.1', ['id' => 10, 'root_id' => 3, 'parent_id' => 3, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F3.2', ['id' => 11, 'root_id' => 3, 'parent_id' => 3, 'lft' => 4, 'rgt' => 25, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F3.2.1', ['id' => 13, 'root_id' => 3, 'parent_id' => 11, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F3.2.2', ['id' => 14, 'root_id' => 3, 'parent_id' => 11, 'lft' => 7, 'rgt' => 8, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F3.2.3', ['id' => 15, 'root_id' => 3, 'parent_id' => 11, 'lft' => 9, 'rgt' => 10, 'depth' => 2, 'priority' => 3]);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 3, 'parent_id' => 11, 'lft' => 11, 'rgt' => 24, 'depth' => 2, 'priority' => 4]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 3, 'parent_id' => 2, 'lft' => 12, 'rgt' => 13, 'depth' => 3, 'priority' => 1]);
        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 3, 'parent_id' => 2, 'lft' => 14, 'rgt' => 21, 'depth' => 3, 'priority' => 2]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 3, 'parent_id' => 5, 'lft' => 15, 'rgt' => 16, 'depth' => 4, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 3, 'parent_id' => 5, 'lft' => 17, 'rgt' => 18, 'depth' => 4, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 3, 'parent_id' => 5, 'lft' => 19, 'rgt' => 20, 'depth' => 4, 'priority' => 3]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 3, 'parent_id' => 2, 'lft' => 22, 'rgt' => 23, 'depth' => 3, 'priority' => 3]);

        $this->validateNodeData('F3.3', ['id' => 12, 'root_id' => 3, 'parent_id' => 3, 'lft' => 26, 'rgt' => 27, 'depth' => 1, 'priority' => 3]);
    }

    public function testMoveRootToDepth2DifferentScopeSubFolder()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 14,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 2), $formData);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 28, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F3.1', ['id' => 10, 'root_id' => 3, 'parent_id' => 3, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F3.2', ['id' => 11, 'root_id' => 3, 'parent_id' => 3, 'lft' => 4, 'rgt' => 25, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F3.2.1', ['id' => 13, 'root_id' => 3, 'parent_id' => 11, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F3.2.2', ['id' => 14, 'root_id' => 3, 'parent_id' => 11, 'lft' => 7, 'rgt' => 22, 'depth' => 2, 'priority' => 2]);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 3, 'parent_id' => 14, 'lft' => 8, 'rgt' => 21, 'depth' => 3, 'priority' => 1]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 3, 'parent_id' => 2, 'lft' => 9, 'rgt' => 10, 'depth' => 4, 'priority' => 1]);
        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 3, 'parent_id' => 2, 'lft' => 11, 'rgt' => 18, 'depth' => 4, 'priority' => 2]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 3, 'parent_id' => 5, 'lft' => 12, 'rgt' => 13, 'depth' => 5, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 3, 'parent_id' => 5, 'lft' => 14, 'rgt' => 15, 'depth' => 5, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 3, 'parent_id' => 5, 'lft' => 16, 'rgt' => 17, 'depth' => 5, 'priority' => 3]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 3, 'parent_id' => 2, 'lft' => 19, 'rgt' => 20, 'depth' => 4, 'priority' => 3]);

        $this->validateNodeData('F3.2.3', ['id' => 15, 'root_id' => 3, 'parent_id' => 11, 'lft' => 23, 'rgt' => 24, 'depth' => 2, 'priority' => 3]);
        $this->validateNodeData('F3.3', ['id' => 12, 'root_id' => 3, 'parent_id' => 3, 'lft' => 26, 'rgt' => 27, 'depth' => 1, 'priority' => 3]);
    }

    public function testMoveSubFolderToGlobal()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 0,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 5), $formData);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 2, 'parent_id' => null, 'lft' => 1, 'rgt' => 6, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 2, 'parent_id' => 2, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 2, 'parent_id' => 2, 'lft' => 4, 'rgt' => 5, 'depth' => 1, 'priority' => 3]);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 14, 'depth' => 0, 'priority' => 3]);

        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 5, 'parent_id' => null, 'lft' => 1, 'rgt' => 8, 'depth' => 0, 'priority' => 4]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 5, 'parent_id' => 5, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 5, 'parent_id' => 5, 'lft' => 4, 'rgt' => 5, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 5, 'parent_id' => 5, 'lft' => 6, 'rgt' => 7, 'depth' => 1, 'priority' => 3]);
    }

    public function testMoveSubFolderToRoot()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 3,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 5), $formData);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 2, 'parent_id' => null, 'lft' => 1, 'rgt' => 6, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 2, 'parent_id' => 2, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 2, 'parent_id' => 2, 'lft' => 4, 'rgt' => 5, 'depth' => 1, 'priority' => 2]);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 22, 'depth' => 0, 'priority' => 3]);
        $this->validateNodeData('F3.1', ['id' => 10, 'root_id' => 3, 'parent_id' => 3, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F3.2', ['id' => 11, 'root_id' => 3, 'parent_id' => 3, 'lft' => 4, 'rgt' => 11, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F3.2.1', ['id' => 13, 'root_id' => 3, 'parent_id' => 11, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F3.2.2', ['id' => 14, 'root_id' => 3, 'parent_id' => 11, 'lft' => 7, 'rgt' => 8, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F3.2.3', ['id' => 15, 'root_id' => 3, 'parent_id' => 11, 'lft' => 9, 'rgt' => 10, 'depth' => 2, 'priority' => 3]);
        $this->validateNodeData('F3.3', ['id' => 12, 'root_id' => 3, 'parent_id' => 3, 'lft' => 12, 'rgt' => 13, 'depth' => 1, 'priority' => 3]);

        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 3, 'parent_id' => 3, 'lft' => 14, 'rgt' => 21, 'depth' => 1, 'priority' => 4]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 3, 'parent_id' => 5, 'lft' => 15, 'rgt' => 16, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 3, 'parent_id' => 5, 'lft' => 17, 'rgt' => 18, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 3, 'parent_id' => 5, 'lft' => 19, 'rgt' => 20, 'depth' => 2, 'priority' => 3]);
    }

    public function testMoveSubFolderToDifferentScopeSubfolder()
    {
        FileNode::boot();

        $this->constructTree();

        $formData = [
            'target_node_id' => 11,
        ];
        $response = $this->action('POST', 'FoldersController@move', array('folderId' => 5), $formData);

        $this->validateNodeData('F2', ['id' => 2, 'root_id' => 2, 'parent_id' => null, 'lft' => 1, 'rgt' => 6, 'depth' => 0, 'priority' => 2]);
        $this->validateNodeData('F2.1', ['id' => 4, 'root_id' => 2, 'parent_id' => 2, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F2.3', ['id' => 6, 'root_id' => 2, 'parent_id' => 2, 'lft' => 4, 'rgt' => 5, 'depth' => 1, 'priority' => 2]);

        $this->validateNodeData('F3', ['id' => 3, 'root_id' => 3, 'parent_id' => null, 'lft' => 1, 'rgt' => 22, 'depth' => 0, 'priority' => 3]);
        $this->validateNodeData('F3.1', ['id' => 10, 'root_id' => 3, 'parent_id' => 3, 'lft' => 2, 'rgt' => 3, 'depth' => 1, 'priority' => 1]);
        $this->validateNodeData('F3.2', ['id' => 11, 'root_id' => 3, 'parent_id' => 3, 'lft' => 4, 'rgt' => 19, 'depth' => 1, 'priority' => 2]);
        $this->validateNodeData('F3.2.1', ['id' => 13, 'root_id' => 3, 'parent_id' => 11, 'lft' => 5, 'rgt' => 6, 'depth' => 2, 'priority' => 1]);
        $this->validateNodeData('F3.2.2', ['id' => 14, 'root_id' => 3, 'parent_id' => 11, 'lft' => 7, 'rgt' => 8, 'depth' => 2, 'priority' => 2]);
        $this->validateNodeData('F3.2.3', ['id' => 15, 'root_id' => 3, 'parent_id' => 11, 'lft' => 9, 'rgt' => 10, 'depth' => 2, 'priority' => 3]);

        $this->validateNodeData('F2.2', ['id' => 5, 'root_id' => 3, 'parent_id' => 11, 'lft' => 11, 'rgt' => 18, 'depth' => 2, 'priority' => 4]);
        $this->validateNodeData('F2.2.1', ['id' => 7, 'root_id' => 3, 'parent_id' => 5, 'lft' => 12, 'rgt' => 13, 'depth' => 3, 'priority' => 1]);
        $this->validateNodeData('F2.2.2', ['id' => 8, 'root_id' => 3, 'parent_id' => 5, 'lft' => 14, 'rgt' => 15, 'depth' => 3, 'priority' => 2]);
        $this->validateNodeData('F2.2.3', ['id' => 9, 'root_id' => 3, 'parent_id' => 5, 'lft' => 16, 'rgt' => 17, 'depth' => 3, 'priority' => 3]);

        $this->validateNodeData('F3.3', ['id' => 12, 'root_id' => 3, 'parent_id' => 3, 'lft' => 20, 'rgt' => 21, 'depth' => 1, 'priority' => 3]);
    }
}
