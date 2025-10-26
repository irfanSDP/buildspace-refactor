<?php

use PCK\TemplateTenderDocumentFolders\TemplateTenderDocumentFolder;

class TemplateTenderDocumentFoldersTableSeeder extends Seeder {

    public function run()
    {
        if( $this->dataExists() )
        {
            return;
        }

        // Only inserts a row as the root
        \DB::table('template_tender_document_folders')->insert(array(
            array(
                'root_id'    => 1,
                'parent_id'  => null,
                'lft'        => 1,
                'rgt'        => 2,
                'depth'      => 0,
                'name'       => TemplateTenderDocumentFolder::ROOT_NAME,
                'created_at' => 'now()',
                'updated_at' => 'now()',
            ),
        ));
    }

    private function dataExists()
    {
        $root = TemplateTenderDocumentFolder::whereRaw('id = root_id')->first();

        if( $root )
        {
            return true;
        }

        return false;
    }
}