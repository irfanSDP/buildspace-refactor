@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('contractManagement.contractManagement') }}</li>
        <li>{{{ $moduleName }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                @foreach($iconClasses as $iconClass)
                    <i class="{{{ $iconClass }}}"></i>
                @endforeach
                {{{ $moduleName }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>
    <?php
        $firstVerifierRecordsByObjectId = [];

        foreach($verifierRecords as $objectId => $vRecords)
        {
            $firstVerifierRecordsByObjectId[$objectId] = $vRecords->first();
        }
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header>
                    <h2> {{{ $moduleName }}} </h2>
                </header>
                <div class="">
                    <p>{{ trans('projects.projectTitle') }}: {{ $project->title }}</p>
                    <div class="widget-body">
                        <table id="review-table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-middle text-center squeeze" style="width:20px;">{{ trans('general.no') }}</th>
                                <th class="text-middle text-center text-nowrap" style="width:180px;">{{{ $moduleName }}}</th>
                                <th class="text-middle text-center text-nowrap squeeze">{{ trans('forms.submittedBy') }}</th>
                                <th class="text-middle text-center text-nowrap squeeze" style="width:180px;">{{ trans('forms.submittedAt') }}</th>
                                <th class="text-middle text-center" style="width:120px;">{{ trans('general.status') }}</th>
                                <th class="text-middle text-center" style="width:100px;">{{ trans('general.view') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php $count = 0;?>
                            @foreach($claimObjects as $objectId => $object)
                                <?php
                                $rowClass = null;
                                $modalHeaderClass = 'bg-color-yellow';
                                $iconClass = 'fa-question';
                                $colour = 'yellow-ochre';
                                $statusText = trans("verifiers.pending");
                                    if(\PCK\Buildspace\ContractManagementClaimVerifier::isApproved($project, $moduleIdentifier, $objectId))
                                    {
                                        $rowClass = 'success';
                                        $modalHeaderClass = 'bg-color-greenLight';
                                        $iconClass = 'fa-thumbs-up';
                                        $colour = 'green';
                                        $statusText = trans("verifiers.approved");
                                    }
                                    else if(\PCK\Buildspace\ContractManagementClaimVerifier::isRejected($project, $moduleIdentifier, $objectId))
                                    {
                                        $rowClass = 'danger';
                                        $modalHeaderClass = 'bg-color-redLight';
                                        $iconClass = 'fa-thumbs-down';
                                        $colour = 'red';
                                        $statusText = trans("verifiers.rejected");
                                    }
                                    else if( \PCK\Buildspace\ContractManagementClaimVerifier::isCurrentVerifier($project, $currentUser, $moduleIdentifier, $objectId) )
                                    {
                                        $rowClass = 'warning';
                                        $modalHeaderClass = 'bg-color-yellow';
                                        $iconClass = 'fa-question';
                                        $colour = 'yellow-ochre';
                                        $statusText = trans("verifiers.pending");
                                    }
                                ?>
                                <tr id="{{{ $objectId }}}" class="{{{ $rowClass }}}">
                                    <td class="text-middle text-center nowrap squeeze">{{{ ++$count }}}</td>
                                    <td class="text-middle text-center nowrap"><span data-toggle="tooltip" title="{{{ $object->displayDescription }}}">{{{ \PCK\Helpers\StringOperations::shorten($object->displayDescription, 25) }}}</span></td>
                                    <td class="text-middle text-center text-nowrap squeeze">{{{ $object->getEprojectUpdatedBy()->name }}}</td>
                                    <td class="text-middle text-center text-nowrap squeeze">{{{ \Carbon\Carbon::parse($project->getProjectTimeZoneTime($firstVerifierRecordsByObjectId[$object->id]['start_at']))->format('d/m/Y g:i a') }}}</td>
                                    <td class="text-middle text-center text-nowrap squeeze {{{ $colour }}}"><i class="fa {{{ $iconClass }}}"></i> <strong>{{{ $statusText }}}</strong></td>
                                    <td class="text-middle text-center text-nowrap squeeze">
                                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#{{{ "verifierStatusOverViewModal-{$objectId}" }}}"><i class="fa fa-search"></i> {{ trans('general.view') }}</button>

                                        @include('contractManagement.generic.claim_item_verifier_details_modal', array('modalId' => "verifierStatusOverViewModal-{$objectId}", 'headerClass' => $modalHeaderClass))
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="{{ asset('js/datatables_all_plugins.min.js') }}"></script>
    <script>
        $('[data-action=submit]').on('click', function(){
            app_progressBar.toggle();
            app_progressBar.maxOut();
        });

        $('#review-table').DataTable({
            sDom: '<f><t>',
            bSort: false,
            paging: false
        });
    </script>
@endsection