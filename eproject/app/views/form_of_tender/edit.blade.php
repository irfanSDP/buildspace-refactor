@extends('layout.main')

@section('css')
    <link href="{{ asset('js/summernote-0.9.0-dist/summernote.min.css')}}" rel="stylesheet">
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        @if($isTemplate)
            <li>{{ trans('formOfTender.formOfTender') }}</li>
            <li>{{ link_to_route('form_of_tender.template.selection', trans('formOfTender.listOfTemplates'), array()) }}</li>
            <li>{{{ $name }}} ({{ trans('formOfTender.template') }})</li>
        @else
            <li>
                <a href="{{ route('projects.show', array($project->id)) }}">{{{ str_limit($project->title, 50) }}}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.index', array($project->id)) }}">{{{ trans('formOfTender.tenders') }}}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.show', array($project->id, $tender->id)) }}">{{{ str_limit($tender->current_tender_name, 50) }}}</a>
            </li>
            <li>{{ trans('formOfTender.formOfTender') }}</li>
        @endif
    </ol>
@endsection

@section('content')

<?php
    isset( $isTemplate ) ?: $isTemplate = false;

    $disabled = ( isset($editable) && (!$editable) );

    if( $isTemplate )
    {
        $title = $name . ' (' . trans('formOfTender.template') . ')';
        $routeHeader = '#';
        $routeAddress = route('form_of_tender.address.template.edit', [$templateId]);
        $routeClauses = route('form_of_tender.clauses.template.edit', [$templateId]);
        $routeTenderAlternatives = route('form_of_tender.tenderAlternatives.template.edit', [$templateId]);
        //$routeTenderAlternatives = '#';
    }
    else
    {
        $title = trans('formOfTender.formOfTender');
        $routeHeader = route('form_of_tender.header.edit', array( $project->id, $tenderId ));
        $routeAddress = route('form_of_tender.address.edit', array( $project->id, $tenderId ));
        $routeClauses = route('form_of_tender.clauses.edit', array( $project->id, $tenderId ));
        $routeTenderAlternatives = route('form_of_tender.tenderAlternatives.edit', array( $project->id, $tenderId ));
    }
?>
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title">
            <i class="fa fa-file-alt"></i>
            {{{ $title }}}
        </h1>
    </div>
</div>
<!-- Widget ID (each widget will need unique ID)-->
<div class="jarviswidget well" id="form_of_tender_edit-content">

    <!-- widget div-->
    <div>
        <!-- widget content -->
        <div class="widget-body">
            <div class="row">
            @if(!$disabled)
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-middle text-center">{{trans('formOfTender.section')}}</th>
                            <th class="text-middle text-center" style="width:120px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!$isTemplate)
                        <tr>
                            <td class="text-middle text-center">{{ trans('formOfTender.header') }}</td>
                            <td class="text-middle text-center">
                                <a href="{{{ $routeHeader }}}" class="btn btn-primary fill-horizontal">
                                    <i class="far fa-edit"></i> {{ trans('forms.edit')}}
                                </a>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td class="text-middle text-center">{{ trans('formOfTender.address') }}</td>
                            <td class="text-middle text-center">
                                <a href="{{{ $routeAddress }}}" class="btn btn-primary fill-horizontal">
                                    <i class="far fa-edit"></i> {{ trans('forms.edit')}}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-middle text-center">{{ trans('formOfTender.clauses') }}</td>
                            <td class="text-middle text-center">
                                <a href="{{{ $routeClauses }}}" class="btn btn-primary fill-horizontal">
                                    <i class="far fa-edit"></i> {{ trans('forms.edit')}}
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-middle text-center">{{ trans('formOfTender.tenderAlternatives') }}</td>
                            <td class="text-middle text-center">
                                <a href="{{{ $routeTenderAlternatives }}}" class="btn btn-primary fill-horizontal">
                                    <i class="far fa-edit"></i> {{ trans('forms.edit') }}
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="alert alert-danger alert-block">
                {{ trans('formOfTender.userUnauthorisedToEdit') }}
                </div>
            @endif
            </div>
            <footer class="row text-right mt-8">
                @if(!$isTemplate)
                    <?php $backRoute = preg_match('/form_of_tender/', URL::previous()) ? route('projects.tender.index', array($projectId)) : URL::previous(); ?>
                    <a href="{{{ $backRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                @endif
                <?php
                    if( $isTemplate )
                    {
                        $settingsRoute = route('form_of_tender.printSettings.template.edit', [$templateId]);
                        $printRoute = route('form_of_tender.template.print', [$templateId]);
                    }
                    else
                    {
                        $settingsRoute = route('form_of_tender.printSettings.edit', array($project->id, $tenderId));
                        $printRoute = route('form_of_tender.print', array($project->id, $tenderId ));
                    }

                    ?>
                <a href="{{{ $printRoute }}}" target="_blank" class="btn btn-success">
                    <i class="fa fa-lg fa-fw fa-print"></i> {{ trans('formOfTender.print') }}
                </a>
                <a href="{{{ $settingsRoute }}}" class="btn btn-success" data-toggle="tooltip" data-placement="left" title="Settings"><i class="fa fa-lg fa-fw fa-cog"></i>{{ trans('formOfTender.settings') }}</a>
                <button type="button" id="showFormOfTenderLog" class="btn btn-default" data-toggle="modal" data-target="#formOfTenderLogModal">{{ trans('general.log') }}</button>
            </footer>
        </div>
        <!-- end widget content -->

    </div>
    <!-- end widget div -->

</div>
<!-- end widget -->

    @include('form_of_tender.partials.log_modal')

@endsection