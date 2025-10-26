<?php namespace PCK\Base;

use PhpOffice\PhpWord\Escaper\Xml;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;

class TemplateProcessorExt extends TemplateProcessor
{
    /**
     * @param mixed $search
     * @param mixed $replace
     * @param int $limit
     *
     * @return void
     */
    public function setValue($search, $replace, $ignoreMacro = false, $limit = self::MAXIMUM_REPLACEMENTS_DEFAULT)
    {
        if(!$ignoreMacro) {
            if (is_array($search)) {
                foreach ($search as &$item) {
                    $item = self::ensureMacroCompleted($item);
                }
            } else {
                $search = self::ensureMacroCompleted($search);
            }
        }


        if (is_array($replace)) {
            foreach ($replace as &$item) {
                $item = self::ensureUtf8Encoded($item);
            }
        } else {
            $replace = self::ensureUtf8Encoded($replace);
        }

        if (Settings::isOutputEscapingEnabled()) {
            $xmlEscaper = new Xml();
            $replace = $xmlEscaper->escape($replace);
        }

        $this->tempDocumentHeaders = $this->setValueForPart($search, $replace, $this->tempDocumentHeaders, $limit);
        $this->tempDocumentMainPart = $this->setValueForPart($search, $replace, $this->tempDocumentMainPart, $limit);
        $this->tempDocumentFooters = $this->setValueForPart($search, $replace, $this->tempDocumentFooters, $limit);
    }
}

