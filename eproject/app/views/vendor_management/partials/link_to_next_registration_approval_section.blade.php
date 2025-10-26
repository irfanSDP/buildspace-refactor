<?php    
    $routes = [
        'companyDetails'           => route('vendorManagement.approval.companyDetails', array($vendorRegistration->id)),
        'registration'             => route('vendorManagement.approval.registration', array($vendorRegistration->id)),
        'companyPersonnel'         => route('vendorManagement.approval.companyPersonnel', array($vendorRegistration->id)),
        'projectTrackRecord'       => route('vendorManagement.approval.projectTrackRecord', array($vendorRegistration->id)),
        'preQualification'         => route('vendorManagement.approval.preQualification', [$vendorRegistration->id]),
        'supplierCreditFacilities' => route('vendorManagement.approval.supplierCreditFacilities', array($vendorRegistration->id)),
        'payment'                  => route('vendorManagement.approval.payment', array($vendorRegistration->company->id)),
    ];

    $sectionOrder = [
        'companyDetails',
        'registration',
        'companyPersonnel',
        'projectTrackRecord',
    ];
    if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_PRE_QUALIFICATION'))
    {
        $sectionOrder[] = 'preQualification';
    }
    if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_SUPPLIER_CREDIT_FACILITIES'))
    {
        $sectionOrder[] = 'supplierCreditFacilities';
    }
    if(!getenv('VENDOR_MANAGEMENT_DISABLE_SECTION_VENDOR_PAYMENT'))
    {
        $sectionOrder[] = 'payment';
    }

    $nextRoute = null;

    $currentIndex = array_search($currentSection, $sectionOrder);

    if($currentIndex !== false)
    {
        $nextSection = $sectionOrder[$currentIndex+1] ?? null;

        $nextRoute = $routes[$nextSection] ?? null;
    }
?>
@if(!is_null($nextRoute))
<a href="{{ $nextRoute }}" class="btn btn-info pull-right">{{ trans('forms.next') }}</a>
@endif