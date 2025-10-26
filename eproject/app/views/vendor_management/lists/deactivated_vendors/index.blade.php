@extends('layout.main')

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{{ trans('vendorManagement.vendorManagement') }}}</li>
        <li>{{{ trans('vendorManagement.deactivatedVendorList') }}}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
        <h1 class="page-title txt-color-blueDark">
            <i class="fa fa-users"></i> {{{ trans('vendorManagement.deactivatedVendorList') }}}
        </h1>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="jarviswidget ">
            <header>
                <h2>{{{ trans('vendorManagement.deactivatedVendorList') }}}</h2>
            </header>
            <div>
                <div class="widget-body">
                    <div id="main-table"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('module_parameters.email_notification_settings.partials.modifiable_contents_modal', [
    'title'   => trans('vendorManagement.updateReminder'),
    'modalId' => 'modifiableContentsModal',
])
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            <?php $canViewVendorProfile = $currentUser->canViewVendorProfile(); ?>
            var mainTable = new Tabulator('#main-table', {
                height:450,
                placeholder: "{{ trans('general.noRecordsFound') }}",
                ajaxURL: "{{ route('vendorManagement.deactivatedVendorList.ajax.list') }}",
                ajaxConfig: "GET",
                paginationSize: 100,
                pagination: "remote",
                ajaxFiltering:true,
                layout:"fitColumns",
                columns:[
                    {title:"{{ trans('general.no') }}", field:"counter", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.name') }}", field:"name", minWidth: 300, hozAlign:'left', headerSort:true, headerFilter: true, formatter:function(cell){
                        @if($canViewVendorProfile)
                            return '<a href="'+cell.getData()['route:view']+'">'+cell.getData()['name']+'</a>';
                        @else
                            return cell.getData()['name'];
                        @endif
                    }},
                    {title:"{{ trans('vendorManagement.vendorCode') }}", field:"vendor_code", width:130, hozAlign:'center', headerFilter: true, cssClass:"text-center text-middle", headerSort:false},
                    {title:"{{ trans('vendorManagement.deactivatedAt') }}", field:"deactivatedAt", width: 150, cssClass:"text-center text-middle", headerSort:true},
                    {title:"{{ trans('general.actions') }}", field:"name", width: 120, cssClass:"text-center text-middle", formatter:app_tabulator_utilities.variableHtmlFormatter, formatterParams: {
                        innerHtml: [
                            {
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-route':'route:reminder'},
                                    attributes: {'class': 'btn btn-xs btn-warning text-white', 'data-action':'send-renewal-reminder'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {title: "{{ trans('vendorManagement.sendRenewalReminder') }}", class: 'fa fa-envelope'}
                                    }
                                }
                            },{
                                innerHtml: function(){
                                    return '&nbsp;';
                                },
                            },,{
                                innerHtml: {
                                    tag: 'button',
                                    rowAttributes: {'data-route':'route:update-reminder'},
                                    attributes: {'class': 'btn btn-xs btn-primary text-white', 'data-action':'send-update-reminder'},
                                    innerHtml: {
                                        tag: 'i',
                                        attributes: {title: "{{ trans('vendorManagement.sendUpdateReminder') }}", class: 'fa fa-envelope'}
                                    }
                                }
                            }
                        ]
                    }},
                ],
            });

            $('#main-table').on('click', '[data-action=send-renewal-reminder]', function(){
                $.post($(this).data('route'), {_token:_csrf_token})
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.sentRenewalReminder')}}");
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                });
            });

            $(document).on('click', '[data-action="send-update-reminder"]', function() {
                $('#modifiableContentsModal [data-action="saveContent"]').data('url', $(this).data('route'));
                $('#modifiableContentsModal [data-action="saveContent"]').prop('disabled', true);
                $('#modifiableContentsModal').modal('show');
            });

            $('#modifiableContentsModal').on('show.bs.modal', function() {
				$('#email_contents').removeAttr('style');
				$('#email_contents').css('height', '200px');
				$('#email_contents').css('overflow-y', 'scroll');
                $('#email_contents').val('');
			});

			$('#modifiableContentsModal').on('shown.bs.modal', function() {
				$('#email_contents').focus();
			});

            $(document).on('input propertychange', '#email_contents', function() {
                var contents = $(this).val().trim();

                var disableFlag = (contents.length > 0) ? false : true;

                $('#modifiableContentsModal [data-action="saveContent"]').prop('disabled', disableFlag);
            });

            $('#modifiableContentsModal [data-action="saveContent"]').on('click', function(e) {
				e.preventDefault();

				var url 	 = $(this).data('url');
				var contents = $('#email_contents').val();

                $.post(url, {
                    contents: DOMPurify.sanitize(contents.trim()),
                    _token:_csrf_token
                })
                .done(function(data){
                    if(data.success){
                        SmallErrorBox.success("{{ trans('general.success') }}", "{{ trans('vendorManagement.sentUpdateReminder')}}");

                        $('#modifiableContentsModal').modal('hide');
                    }
                })
                .fail(function(){
                    SmallErrorBox.refreshAndRetry();
                });
			});
        });
    </script>
@endsection