@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), []) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), [$project->id]) }}</li>
        <li>{{ link_to_route('projects.questionnaires.index', trans('general.questionnaires'), [$project->id]) }}</li>
        <li>{{ link_to_route('projects.questionnaires.show', str_limit($company->name, 50), [$project->id, $company->id]) }}</li>
        <li>{{{ trans('general.questionnaire') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-tasks"></i> {{{ trans('general.questionnaire') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2><i class="fa fa-fw fa-tasks"></i> {{{ $company->name }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div class="row">
                        <div class="col col-lg-12">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{ trans('general.question') }}:</dt>
                                <dd>{{ $question->question }}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>Mandatory:</dt>
                                <dd>{{{ ($question->required) ? trans('general.yes') : trans('general.no') }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>{{{ trans('general.type') }}}:</dt>
                                <dd>{{{ $question->getTypeText() }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                            <dl class="dl-horizontal no-margin">
                                <dt>With Attachment:</dt>
                                <dd>{{{ ($question->with_attachment) ? trans('general.yes') : trans('general.no') }}}</dd>
                                <dt>&nbsp;</dt>
                                <dd>&nbsp;</dd>
                            </dl>
                        </div>
                    </div>

                    @if($question->type == PCK\ContractorQuestionnaire\Question::TYPE_MULTI_SELECT or $question->type == PCK\ContractorQuestionnaire\Question::TYPE_SINGLE_SELECT)
                    <hr class="simple">

                    <div class="row">
                        <section class="col col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <h5>{{{trans('documentManagementFolders.options')}}}</h5>
                        </section>
                    </div>
                    <div class="row">
                        <section class="col col-xs-12 col-md-12 col-lg-12">
                            <table class="table table-bordered table-condensed table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width:38px;text-align:center;">No.</th>
                                        <th style="width:auto;">{{{trans('documentManagementFolders.options')}}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($question->options as $idx => $option)
                                    <tr>
                                        <td class="text-middle text-center squeeze">{{$idx+1}}</td>
                                        <td>{{{ $option->text }}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </section>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col col-lg-12">
                            <div class="pull-right">
                            @if($question && $question->deletable() && $isEditor)
                            {{ HTML::decode(link_to_route('projects.questionnaires.question.edit', '<i class="fa fa-edit"></i> '.trans('forms.edit'), [$project->id, $question->id], ['class' => 'btn btn-primary'])) }}
                            {{ HTML::decode(link_to_route('projects.questionnaires.question.delete', '<i class="fa fa-trash"></i> '.trans('forms.delete'), [$project->id, $question->id], ['data-id'=>$question->id, 'data-method'=>"delete", 'data-csrf_token'=>csrf_token(), 'class' => 'btn btn-danger'])) }}
                            @endif
                            {{ link_to_route('projects.questionnaires.show', trans('forms.back'), [$project->id, $company->id], ['class' => 'btn btn-default']) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="<?php echo asset('js/app/app.restfulDelete.js'); ?>"></script>
@endsection