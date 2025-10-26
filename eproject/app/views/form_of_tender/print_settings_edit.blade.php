@extends('layout.main')

@section('css')
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        @if($isTemplate)
            <li>{{ trans('formOfTender.formOfTender') }}</li>
            <li>{{ link_to_route('form_of_tender.template.selection', trans('formOfTender.listOfTemplates'), array()) }}</li>
            <li>{{ link_to_route('form_of_tender.template.edit', $templateName . ' (' . trans('formOfTender.template') . ')', array($templateId)) }}</li>
        @else
            <li>
                <a href="{{ route('projects.show', array($project->id)) }}">{{{ str_limit($project->title, 50) }}}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.index', array($project->id)) }}">{{ trans('formOfTender.tenders') }}</a>
            </li>
            <li>
                <a href="{{ route('projects.tender.show', array($project->id, $tender->id)) }}">{{{ str_limit($tender->current_tender_name, 50) }}}</a>
            </li>
            <li><a href="{{{ $backRoute }}}">{{ trans('formOfTender.formOfTender') }}</a></li>
        @endif
        <li>{{ trans('formOfTender.printSettings') }}</li>
    </ol>
@endsection

@section('content')
    <article class="col-sm-12">

        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <h1 class="page-title">
                    <i class="fa fa-lg fa-fw fa-cog"></i>
                    @if($isTemplate)
                        {{ trans('formOfTender.formOfTender') }} {{ trans('formOfTender.printSettings') }} ({{ trans('formOfTender.template') }})
                    @else
                        {{ trans('formOfTender.formOfTender') }} {{ trans('formOfTender.printSettings') }}
                    @endif
                </h1>
            </div>
        </div>

        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget well">

            <!-- widget div-->
            <div>
                <!-- widget content -->
                <div class="widget-body">

                    @if($isTemplate)
                        {{ Form::model($settings, array('route'=>array('form_of_tender.printSettings.template.update', $templateId), 'method' => 'post', 'class' => 'smart-form')) }}
                    @else
                        {{ Form::model($settings, array('route'=>array('form_of_tender.printSettings.update', $project->id, $tender->id), 'method' => 'post', 'class' => 'smart-form')) }}
                    @endif
                    <div class="row">

                        <div class="col col-xs-6 col-md-6 col-lg-6">
                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="font_size" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.fontSize') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('font_size', Input::old('font_size'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.fontSize'), 'autofocus')) }}
                                        {{ $errors->first('font_size', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="margin_top" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.topMargin') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('margin_top', Input::old('margin_top'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.topMargin'), 'autofocus')) }}
                                        {{ $errors->first('margin_top', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="margin_bottom" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.bottomMargin') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('margin_bottom', Input::old('margin_bottom'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.bottomMargin'))) }}
                                        {{ $errors->first('margin_bottom', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="margin_left" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.leftMargin') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('margin_left', Input::old('margin_left'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.leftMargin'))) }}
                                        {{ $errors->first('margin_left', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="margin_right" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.rightMargin') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('margin_right', Input::old('margin_right'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.rightMargin'))) }}
                                        {{ $errors->first('margin_right', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>
                        </div>

                        <div class="col col-xs-6 col-md-6 col-lg-6">
                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="include_header_line" class="col-sm-4 col-md-4 col-lg-4 label">{{ trans('formOfTender.horizontalLineAfterHeader') }}</label>
                                    <label class="col-sm-2 col-md-2 col-lg-2">
                                        <input type="radio" name="include_header_line" value="1" class="" {{{ $settings->include_header_line ? 'checked' : '' }}}>
                                        {{ trans('forms.include') }}
                                    </label>
                                    <label class="col-sm-2 col-md-2 col-lg-2">
                                        <input type="radio" name="include_header_line" value="0" class="" {{{ $settings->include_header_line ? '' : 'checked' }}}/>
                                        {{ trans('forms.exclude') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="header_spacing" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.headerSpacing') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('header_spacing', Input::old('header_spacing'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.headerSpacing'))) }}
                                        {{ $errors->first('header_spacing', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="footer_text" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.title') }}</label>
                                    <label class="input col-sm-8 col-md-8 col-lg-8">
                                        {{ Form::text('title_text', Input::old('title_text'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.title'))) }}
                                        {{ $errors->first('title_text', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="footer_text" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.footerText') }}</label>
                                    <label class="input col-sm-8 col-md-8 col-lg-8">
                                        {{ Form::text('footer_text', Input::old('footer_text'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.footerText'))) }}
                                        {{ $errors->first('footer_text', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>

                            <div class="row">
                                <section class="form-group col col-xs-12 col-md-12 col-lg-12">
                                    <label for="footer_font_size" class="col-sm-2 col-md-2 col-lg-2 label">{{ trans('formOfTender.footerFontSize') }}</label>
                                    <label class="input col-sm-2 col-md-2 col-lg-2">
                                        {{ Form::text('footer_font_size', Input::old('footer_font_size'), array('class' => 'form-control', 'placeholder' => trans('formOfTender.footerFontSize'))) }}
                                        {{ $errors->first('footer_font_size', '<em class="invalid">:message</em>') }}
                                    </label>
                                </section>
                            </div>
                        </div>
                    </div>
                    <footer class="row text-right mt-8">
                        <a href="{{{ $backRoute }}}" class="btn btn-default">{{ trans('forms.back') }}</a>
                        <button class="btn btn-primary"><i class="fa fa-save"></i> {{ trans('forms.save') }}</button>
                    </footer>
                    {{ Form::close() }}
                </div>
                <!-- end widget content -->

            </div>
            <!-- end widget div -->

        </div>
        <!-- end widget -->

    </article>
@endsection
