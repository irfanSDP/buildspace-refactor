<?php
class sfBuildspaceExportLibraryXML extends sfBuildspaceXMLGenerator
{
    public $xml;
    public $elements = false;
    public $items = false;
    public $currentTradeChild;
    public $currentItemChild;

    function __construct( $filename = null, $uploadPath = null, $extension = null, $deleteFile = null ) {
        parent::__construct( $filename, $uploadPath, $extension, $deleteFile );
    }

    public function createBillXML( $elements = false, $items = false ) {

        parent::create( 'bill' );

        if ( $elements )
            $this->createTradeTag();

        if ( $items )
            $this->createItemTag();
    }

    public function createTradeTag() {
        $this->elements = parent::createTag( 'TRADES' );
    }

    public function addTradeChildren( $fieldAndValues ) {
        $this->currentTradeChild = parent::addChildTag( $this->elements, 'item', $fieldAndValues );
    }

    public function createItemTag() {
        $this->items = parent::createTag( 'ITEMS' );
    }

    public function addItemChildren( $fieldAndValues ) {
        $this->currentItemChild = parent::addChildTag( $this->items, 'item', $fieldAndValues );
    }

}
