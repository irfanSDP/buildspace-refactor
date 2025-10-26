<?php namespace PCK\Helpers;

class PdfHelper {

    protected $view;
    protected $data;
    protected $fileName;
    protected $headerHtml = '';
    protected $pdfOptions = '';

    public function __construct($view, $data, $fileName = 'file')
    {
        $this->view     = $view;
        $this->data     = $data;
        $this->fileName = $fileName;
    }

    public function setHeaderHtml($headerHtml)
    {
        $this->headerHtml = $headerHtml;
    }

    public function setOptions($pdfOptions)
    {
        $this->pdfOptions = $pdfOptions;
    }

    private function replaceUnderlineCSS()
    {
        array_walk_recursive($this->data, function(&$value, $key)
        {
            $value = str_replace('text-decoration-line', 'text-decoration', $value);
        });
    }

    public static function removeBreaksFromHtml($input)
    {
        $input = preg_replace("~<!--(.*?)-->~s", '', $input);//to remove any html comments (suports multiline comments)
        $input = preg_replace("/<p[^>]*><\\/p[^>]*>/", '', $input);//to remove any empty paragraph tags

        $output    = str_replace(array( "\r\n", "\r" ), "\n", trim($input));
        $lines     = explode("\n", $output);
        $new_lines = array();

        foreach($lines as $i => $line)
        {
            if( ! empty( $line ) ) $new_lines[] = trim($line);
        }

        return implode($new_lines);
    }

    public function printPDF()
    {
        $this->replaceUnderlineCSS();

        if( ! empty( $this->headerHtml ) ) \PDF::setHeaderHtml($this->headerHtml);
        if( ! empty( $this->pdfOptions ) ) \PDF::setOptions($this->pdfOptions);

        return \PDF::html($this->view, $this->data, $this->fileName);
    }
}