<?php
// Connection Component Binding
Doctrine_Manager::getInstance()->bindComponent('EProjectClaimCertificatePayment', 'eproject_conn');
Doctrine_Manager::getInstance()->getConnectionForComponent('EProjectClaimCertificatePayment')->setAttribute(Doctrine_Core::ATTR_TBLNAME_FORMAT, '%s');

abstract class BaseEProjectClaimCertificatePayment extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('claim_certificate_payments');
        $this->hasColumn('id', 'integer', null, array(
            'type' => 'integer',
            'primary' => true,
            'autoincrement' => true
        ));
        $this->hasColumn('claim_certificate_id', 'integer', null, array(
            'type' => 'integer',
            'notnull' => true
        ));
        $this->hasColumn('amount', 'decimal', null, array(
            'type' => 'decimal',
            'scale' => 2,
            'default' => 0,
        ));

        $this->option('orderBy', 'id');
        $this->option('symfony', array(
            'filter' => false,
            'form'   => false
        ));
    }
}
