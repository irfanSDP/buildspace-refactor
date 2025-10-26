@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ trans('contractManagement.contractManagement') }}</li>
        <li>{{{ trans('contractManagement.publishToPostContract') }}}</li>
    </ol>
@endsection

@section('content')

    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-envelope "></i> {{{ trans('contractManagement.publishToPostContract') }}}
            </h1>
        </div>
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget">
                <header class="rounded-less-nw">
                    <h2> {{{ trans('contractManagement.publishToPostContract') }}} </h2>
                </header>
                <div>
                    <div class="widget-body">
                        <form class="smart-form">
                            @include('verifiers.verifier_status_overview', array('additionalFields' => array('Days Pending' => 'daysPending')))
                        </form>
                    </div>
                    @if(\PCK\Buildspace\ContractManagementVerifier::isCurrentVerifier($project, $currentUser, PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD))
                        <div class="widget-footer">
                            <?php $buildspaceLink = getenv('BUILDSPACE_URL') . "?bsApp=" . \PCK\Buildspace\Menu::BS_APP_IDENTIFIER_APPROVAL . "&id={$project->id}&module_identifier=" . PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD ;?>
                            <a href="{{{ $buildspaceLink }}}" class="btn btn-default"><i class="fa fa-search"></i>&nbsp;{{ trans('verifiers.inspect') }}</a>
                        </div>
                    @elseif(\PCK\Filters\PostContractLetterOfAwardFilters::canSubstitute($project, $currentUser))
                        <div class="widget-footer">
                            <?php $currentVerifier = \PCK\Buildspace\ContractManagementVerifier::getCurrentVerifier($project, PCK\Buildspace\PostContractClaim::TYPE_LETTER_OF_AWARD) ?>
                            <a data-intercept="confirmation" class="btn btn-danger" href="{{ route('contractManagement.letterOfAward.substituteAndReject', array($project->id, $currentVerifier->id)) }}">
                                <i class="fa fa-thumbs-down"></i>
                                {{ trans('contractManagement.rejectAsSubstitute') }}
                            </a>
                            <a data-intercept="confirmation" class="btn btn-success" href="{{ route('contractManagement.letterOfAward.substituteAndApprove', array($project->id, $currentVerifier->id)) }}">
                                <i class="fa fa-thumbs-up"></i>
                                {{ trans('contractManagement.approveAsSubstitute') }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')

    <script>
        $('[data-action=submit]').on('click', function(){
            app_progressBar.toggle();
            app_progressBar.maxOut();
        });
    </script>
@endsection