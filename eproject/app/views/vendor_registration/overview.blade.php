@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorRegistration') }}}</li>
        <li>{{{ trans('vendorManagement.overview') }}}</li>
    </ol>

    <span class="ribbon-button-alignment pull-right">
        <span class="label label-success" style="font-size:small;">{{{ $currentUser->company->vendorRegistration->status_text }}}</span>
        <span class="label label-info" style="font-size:small;">{{{ $currentUser->company->vendorRegistration->submission_type_text }}}</span>
    </span>
@endsection

@section('content')

<div id="content">
    @if($currentUser->isTemporaryAccount())
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 well">
            This account expires at <strong>{{ $currentUser->purge_date->format(\Config::get('dates.readable_timestamp')) }}</strong>
        </div>
    </div>
    @endif
    <div class="row">
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <h1 class="page-title txt-color-blueDark">
                <i class="fa fa-address-book"></i> {{{ trans('vendorManagement.overview') }}}
            </h1>
        </div>
    </div>
    @if($vendorRegistration->isFirstRevision() && $vendorRegistration->unsuccessful_at)
    <div class="row">
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="alert alert-warning fade in">
                <i class="fa-fw fa fa-info"></i>
                <strong>Info!</strong> {{ trans('vendorManagement.validSubmissionPeriodEnded') }}
            </div>
        </article>
    </div>
    @else
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget ">
                <header>
                    <h2>{{{ trans('vendorManagement.overview') }}}</h2>
                </header>
                <div>
                    <div class="widget-body">
                        @if(!$vendorRegistration->isCompleted() && !empty($vendorRegistration->getProcessorRemarks()))
                        <div class="well border-danger text-danger">
                            {{ nl2br($vendorRegistration->getProcessorRemarks()) }}
                        </div>
                        <br/>
                        @endif
                        <div id="main-table"></div>
                        <footer class="pull-right">
                            @if($canChangeVendorGroup)
                                <a href="{{ route('vendors.vendorRegistration.vendorGroup.edit') }}" type="button" class="btn btn-warning">{{ trans('vendorManagement.changeVendorGroup') }}</a>
                            @endif
                            @if($currentUser->company->vendorRegistration->isDraft())
                                @if(!$currentUser->company->vendorRegistration->isFirst())
                                    <button id="discardButton" type="button" class="btn btn-danger" data-action="form-submit" data-target-id="discard-changes-form">{{ trans('forms.discardChanges') }}</button>
                                @endif
                                <a href="{{ route('vendors.vendorRegistration.edit') }}" class="btn btn-primary">{{ trans('forms.submit') }}</a>
                            @endif
                            @if($canRenew)
                                {{ Form::open(array('route' => 'vendors.vendorRegistration.startRenewal', 'id' => 'renewalForm') ) }}
                                <button type="submit" class="btn btn-primary">{{ trans('vendorManagement.renew') }}</button>
                                {{ Form::close() }}
                            @elseif($canUpdateExistingRegistration)
                                {{ Form::open(array('route' => 'vendors.vendorRegistration.startUpdate', 'id' => 'updateForm') ) }}
                                <button type="submit" class="btn btn-primary">{{ trans('forms.update') }}</button>
                                {{ Form::close() }}
                            @endif
                        </footer>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@if($currentUser->company->vendorRegistration->isDraft() && !$currentUser->company->vendorRegistration->isFirst())
{{ Form::open(array('route' => 'vendors.vendorRegistration.discardDraftRevision', 'id' => 'discard-changes-form') ) }}
{{ Form::close() }}
@endif

@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('#renewalForm').submit(function(){
                $(this).find(':button[type=submit]').prop('disabled', true);
            });

            $('#updateForm').submit(function(){
                $(this).find(':button[type=submit]').prop('disabled', true);
            });

            $('#discard-changes-form').submit(function(){
                $('#discardButton').prop('disabled', true);
            });
            
            var descriptionFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                var descriptionLabel = document.createElement('label');
                descriptionLabel.innerText = data.description;

                var container = document.createElement('div');
                container.appendChild(descriptionLabel);

                if(data.hasOwnProperty('hasErrors') && data.hasErrors) {
                    container.style.backgroundColor = '#FDD6D6';
                }
                else if(data.hasOwnProperty('hasChanges') && data.hasChanges) {
                    container.style.backgroundColor = '#ffc241';
                }

				return container;
			}

            var applicabilityFormatter = function(cell, formatterParams, onRendered) {
                var data = cell.getRow().getData();

                if(data.isApplicable == null) return null;

                var buttonClass = data.isApplicable ? 'btn-success' : 'btn-danger';

				var toggleApplicabilityButton = document.createElement('button');
                toggleApplicabilityButton.className = 'btn btn-xs ' + buttonClass;
				toggleApplicabilityButton.innerHTML = data.isApplicable ? '<i class="fas fa-check"></i>' : '<i class="fas fa-times"></i>';
				toggleApplicabilityButton.style['margin-right'] = '5px';
                toggleApplicabilityButton.dataset.url = data['route:toggleApplicability'];

                toggleApplicabilityButton.addEventListener('click', function(e) {
                    e.preventDefault();

                    var url = this.dataset.url;

                    $.post(url, {
                        _token: _csrf_token,
                    })
                    .done(function(data){
                        if(data.success) {
                            location.reload()
                        }
                    })
                    .fail(function(data){
                        console.error('failed');
                    });
                });

				return toggleApplicabilityButton;
			}

            var mainTable = new Tabulator('#main-table', {
                height:300,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                data: {{ json_encode($data) }},
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('general.description') }}", field:"description", minWidth: 300, hozAlign:"left", headerSort:false, formatter:descriptionFormatter },
                    @if($vendorRegistration->isDraft())
                    {title: "{{ trans('vendorManagement.applicable') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter: applicabilityFormatter },
                    @endif
                    {title:"{{ trans('general.actions') }}", width: 120, hozAlign:"center", cssClass:"text-center text-middle", headerSort:false, formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        show: function(cell){
                            return cell.getData().hasOwnProperty('id');
                        },
                        innerHtml: [
                            {
                                show: function(cell){
                                    return cell.getData()['route:view'];
                                },
                                tag: 'a',
                                rowAttributes: {href:'route:view'},
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("general.view") }}'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'}
                                }
                            },{
                                show: function(cell){
                                    return !cell.getData()['route:view'];
                                },
                                tag: 'button',
                                attributes: {class:'btn btn-xs btn-default', title: '{{ trans("general.view") }}', disabled:'disabled'},
                                innerHtml: {
                                    tag: 'i',
                                    attributes: {class: 'fa fa-arrow-right'}
                                }
                            }
                        ]
                    }}
                ],
            });
        });
    </script>
@endsection