@extends('layout.main')

@section('css')
<style>
    .parent-clause-numbering {
        vertical-align: text-top;
        font-size: 13px;
    }

    .contents {
        padding-left: 10px;
        font-size: 12px;
    }

    .no-left-padding {
        padding-left: 0;
    }

    .standard-font-size {
        font-size: 12px;
    }

    .signature-padding {
        padding-left: 20px;
    }
    
    .new-page {
        page-break-before: always;
    }

    .bolded {
        font-weight: bold;
    }

    .root-clause-spacing {
        padding-top: 14px;
        padding-bottom: 10px;
    }

    .child-clause-spacing {
        padding-bottom: 10px;
    }
</style>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('home.index', trans('navigation/mainnav.home')) }}</li>
        <li>{{ link_to_route('consultant.management.loa.templates.index', 'LOA Templates') }}</li>
        <li>{{{ $template->short_title }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-file-code"></i> LOA Template
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-file-code"></i> {{{ $template->title }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <ul id="consultant-management-loa-tabs" class="nav nav-tabs bordered">
                                <li class="active">
                                    <a href="#consultant-management-loa-tab-letter-head" data-toggle="tab"><i class="fa fa-fw fa-lg fa-certificate"></i> {{{ trans('letterOfAward.letterHead') }}}</a>
                                </li>
                                <li>
                                    <a href="#consultant-management-loa-tab-clauses" data-toggle="tab"><i class="fa fa-fw fa-lg fa-align-left"></i> {{{ trans('letterOfAward.clauses') }}}</a>
                                </li>
                                <li>
                                    <a href="#consultant-management-loa-tab-signatory" data-toggle="tab"><i class="fa fa-fw fa-lg fa-signature"></i> {{{ trans('letterOfAward.signatory') }}}</a>
                                </li>
                            </ul>
                            <div id="consultant-management-load-tab-content" class="tab-content padding-10">
                                <div class="tab-pane fade in active " id="consultant-management-loa-tab-letter-head">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="pull-right" style="padding-bottom:12px;">
                                            {{ HTML::decode(link_to_route('consultant.management.loa.templates.letterhead.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$template->id], ['class' => 'btn btn-primary'])) }}
                                            </div>
                                        </div>
                                    </div>
                                    @if(strlen($template->letterhead) > 0)
                                    <div class="well">
                                    {{ $template->letterhead }}
                                    </div>
                                    @else
                                    <div class="alert text-middle text-center alert-warning">Header is empty</div>
                                    @endif
                                </div>
                                <div class="tab-pane fade in" id="consultant-management-loa-tab-clauses">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="pull-right" style="padding-bottom:12px;">
                                            {{ HTML::decode(link_to_route('consultant.management.loa.templates.clause.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$template->id], ['class' => 'btn btn-primary'])) }}
                                            </div>
                                        </div>
                                    </div>
                                    @if($template->clauses->count() > 0)
                                    <div class="well">
                                        <table>{{$clauseHtml}}</table>
                                    </div>
                                    @else
                                    <div class="alert text-middle text-center alert-warning">Clauses is empty</div>
                                    @endif
                                </div>
                                <div class="tab-pane fade in" id="consultant-management-loa-tab-signatory">
                                    <div class="row">
                                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                                            <div class="pull-right" style="padding-bottom:12px;">
                                            {{ HTML::decode(link_to_route('consultant.management.loa.templates.signatory.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$template->id], ['class' => 'btn btn-primary'])) }}
                                            </div>
                                        </div>
                                    </div>
                                    @if(strlen($template->signatory) > 0)
                                    <div class="well">
                                    {{ $template->signatory }}
                                    </div>
                                    @else
                                    <div class="alert text-middle text-center alert-warning">Signatory is empty</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <br />
                    </div>
                    <hr class="simple">
                    <footer>
                        <div class="pull-right">
                        {{ HTML::decode(link_to_route('consultant.management.loa.templates.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$template->id], ['class' => 'btn btn-primary'])) }}
                        @if($template && $template->deletable())
                        {{ HTML::decode(link_to_route('consultant.management.loa.templates.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$template->id], ['data-id'=>$template->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                        @endif
                        {{ HTML::decode(link_to_route('consultant.management.loa.templates.preview', '<i class="fa fa-eye"></i> '.trans('general.preview'), [$template->id], ['class' => 'btn btn-info', 'target'=>"_blank"])) }}
                        {{ link_to_route('consultant.management.loa.templates.index', trans('forms.back'), [], ['class' => 'btn btn-default']) }}
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>

@endsection