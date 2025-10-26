<?php

class FileDownloadTest extends TestCase {

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testValidAttachmmentFilenames()
    {
        $filenames = [
            '%sample%',
            "testing?[]=<>:;,'\\&!@#$%^&*(){}[]-=:]\";',./<>?|\ttesting",
            "?[]=<>:;,'\\&!@#$%^&*(){}[]-=:]\";',./<>?|\t",
        ];

        $extensions = ['ebq', 'zip', 'txt'];

        $fullFilenames = [];

        foreach($filenames as $filename)
        {
            $fullFilenames[] = $filename;

            foreach($extensions as $extension)
            {
                $fullFilenames[] = $filename.'.'.$extension;
            }
        }

        foreach($fullFilenames as $fullFilename)
        {
            try
            {
                \PCK\Helpers\Files::download(
                    base_path().'/server.php', // random file that's always there
                    $fullFilename, array(
                    'Content-Type: attachment',
                ));

                self::assertTrue(true);
            }
            catch(\Exception $e)
            {
                self::assertTrue(false);
            }
        }
    }

}
