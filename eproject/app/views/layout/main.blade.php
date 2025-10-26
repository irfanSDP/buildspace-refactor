<!DOCTYPE html>
<html lang="en-us">
<head>
	@include('layout.main_partials.head')
</head>
<body class="smart-style-0 fixed-header fixed-navigation @if(!$isLicenseValid) hidden-menu @endif">
	<script type="text/javascript">const _csrf_token = "{{ csrf_token() }}";</script>
	<script src="{{ asset('js/jquery/dist/jquery.min.js') }}"></script>
	<script src="{{ asset('js/jquery/dist/jquery-migrate-3.5.2.min.js') }}"></script>
	<script src="{{ asset('js/jquery-ui-1.14.0/jquery-ui.min.js') }}"></script>
	<script src="{{ asset('js/vue/dist/vue.min.js') }}"></script>
	<script src="{{ asset('js/app/app.tabulatorUtilities.js') }}"></script>
	<script src="{{ asset('js/tabulator/tabulator.min.js') }}"></script>
	<script src="{{ asset('js/app/modalStack.js') }}"></script>
	@include('layout.partials.header')

	@if($isLicenseValid)
		@include('layout.partials.navigation')
	@endif

	<div id="main" role="main">

		@if(!(isset($hide_ribbon) && $hide_ribbon == true))
			<div id="ribbon">
				@yield('breadcrumb')
			</div>
		@endif

		<div id="content">
			@include('layout.partials.flash_message')

			@yield('content')
		</div>
	</div>

	@include('templates.app_confirmation_modal')
	@include('templates.progressbarModal')
	@include('layout.partials.my_processes_modal')
	<div class="modal fade" id="restful_delete-modal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h3 class="modal-title">{{trans('general.confirmation')}}</h3>
				</div>
				<div class="modal-body smart-form">
					<div class="alert alert-warning text-center"><i class="fa-fw fa fa-exclamation-triangle"></i> <strong><span></span></strong></div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-default" id="restful_delete_no-btn" type="button" data-dismiss="modal">{{trans('forms.cancel')}}</button>
					<button class="btn btn-danger" id="restful_delete_yes-btn" type="button" data-dismiss="modal"><i class="fa fa-times"></i> {{trans('general.delete')}}</button>
				</div>
			</div>
		</div>
	</div>
	<!-- IMPORTANT: APP CONFIG -->
	<script src="{{ asset('js/app.config.js') }}"></script>

	<script src="{{ asset('js/bootstrap/bootstrap.min.js') }}"></script>
	<script src="{{ asset('js/notification/SmartNotification.min.js') }}"></script>
	<script src="{{ asset('js/smartwidgets/jarvis.widget.min.js') }}"></script>
	<script src="{{ asset('js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js') }}"></script>
	<script src="{{ asset('js/plugin/select2/js/select2.min.js') }}"></script>
	<script src="{{ asset('js/plugin/bootstrap-slider/bootstrap-slider.min.js') }}"></script>
	<script src="{{ asset('js/plugin/msie-fix/jquery.mb.browser.min.js') }}"></script>
	<script src="{{ asset('js/plugin/slimscroll/jquery.slimscroll.min.js') }}"></script>
	<script src="{{ asset('js/plugin/jquery-number/jquery.number.min.js') }}"></script>
	<!--[if IE 8]>

	<h1>Your browser is out of date, please update your browser by going to www.microsoft.com/download</h1>

	<![endif]-->

	<!-- MAIN APP JS FILE -->
	@if(Config::get('app.debug', false))
	<script src="{{ asset('js/app.js') }}"></script>
	@else
	<script src="{{ asset('js/app.min.js') }}"></script>
	@endif

    <!-- DOMpurify--->
	<script src="{{ asset('js/purify.min.js') }}"></script>

	<!-- Sortable JS -->
	<script src="{{ asset('js/Sortable.min.js') }}"></script>

	<!-- confirmation.js has to be placed before page-specific javascript, to ensure that it will be triggered first. -->
	<script src="{{ asset('js/app/app.confirmation.js') }}"></script>
	<script src="{{ asset('js/app/app.cookieHelpers.js') }}"></script>
	<script src="{{ asset('js/jquery.autosize.min.js') }}"></script>
	<script src="{{ asset('js/app/app.datePicker.js') }}"></script>
	<script src="{{ asset('js/app/app.expandable.js') }}"></script>
	<script src="{{ asset('js/app/app.progressBar.js') }}"></script>
	<script src="{{ asset('js/app/app.pageHashHistory.js') }}"></script>
	<script src="{{ asset('js/app/app.smallErrorBox.js') }}"></script>
    <script src="{{ asset('js/app/cust-tabulator.js') }}"></script>
    <script src="{{ asset('js/app/dynamicBreadcrumbs.js') }}"></script>

	<!-- Upload file modal -->
    <script src="{{ asset('js/jquery_file_upload/tmpl.min.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/load-image.all.min.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/canvas-to-blob.min.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.iframe-transport.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-process.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-image.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-audio.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-video.js') }}"></script>
    <script src="{{ asset('js/jquery_file_upload/jquery.fileupload-validate.js') }}"></script>
	<script src="{{ asset('js/jquery_file_upload/jquery.fileupload-ui.js') }}"></script>
	
	<!--idle timeout-->
	<script src="{{ asset('js/store.min.js') }}" type="text/javascript"></script>
	@if(Config::get('app.debug', false))
	<script src="{{ asset('js/idle-timeout/idle.timeout.js') }}" type="text/javascript"></script>
	@else
	<script src="{{ asset('js/idle-timeout/idle.timeout.min.js') }}" type="text/javascript"></script>
	@endif

	@include('layout.partials.javascript_variable_holder')
	@include('layout.partials.my_processes_modal_js')

	<script type="text/javascript">
		$(document).ready(function () {

			pageSetUp();

			var hHeight = $('#header').height();
			var lHeight = $('.login-info').height();
			var wHeight = $(window).height();
			$('#navigation-menu').slimScroll({
				height: (wHeight - (hHeight+lHeight+12))+'px',
				color: '#ffffff',
				railColor: '#333333',
				size: '4px',
				distance: '4px',
				opacity: 0.4,
				railOpacity: 0.4,
				borderRadius: '7px'
			});

			$('body').addClass(localStorage.getItem('bodyClass'));

			autosize($('textarea'));

			$(document).on('click', 'table th .checkall', function(){
				if($(this).is(':checked')){
					$(this).closest('table').find(':checkbox').prop('checked', true);
				}
				else{
					$(this).closest('table').find(':checkbox').prop('checked', false);
				}
			});

			$(document).on('click', '.checkall-channeler', function(){
				if($(this).is(':checked')){
					$(document).find('[data-channel=' + $(this).data('target') + ']:checkbox').prop('checked', true);
				}
				else{
					$(document).find('[data-channel=' + $(this).data('target') + ']:checkbox').prop('checked', false);
				}
			});

			$('.nav-menu-header-dropdown').slimScroll({
				height: '320px',
				color: 'rgb(0, 0, 0, 0.6)',
				railColor: '#fafafa',
				size: '4px',
				distance: '4px',
				opacity: 0.4,
				railOpacity: 0.4,
				borderRadius: '7px'
			});

			$(document).on('focus', 'input[type=text],input[type=number]', function(){
				$(this).select();
			});

			$('a[data-type="form-submit"]').on('click', function(){
				$('form#'+$(this).data('target')).submit();
			});

			// Button form submitter.
			$(document).on('click', '[data-action=form-submit]', function(){
				$('form#'+$(this).data('target-id')).submit();
			});

			$(document).on('click', '[data-trigger=form][data-action]', function(){
				var form =
		            $('<form>', {
		                'method': 'POST',
		                'action': $(this).data('action')
		            });

		        var token =
		            $('<input>', {
		                'type': 'hidden',
		                'name': '_token',
		                'value': $(this).data('csrf_token')
		            });

		        var hiddenInput =
		            $('<input>', {
		                'name': '_method',
		                'type': 'hidden',
		                'value': $(this).data('method')
		            });

		        form.append(token, hiddenInput).appendTo('body');

		        form.submit();
			});

			$("[data-type=action-button-menu]").each(function(){
				if($(this).siblings("ul[data-type=action-button-menu-list]").children('li').length < 1) $(this).hide();
			});

			function hideEmptyNavbarLists(){
				// We process the descendants bottom-up so that we remove parents after their children has been removed.
				$($('nav ul').get().reverse()).each(function(){
					if($(this).children('li').length < 1) $(this).parent('li').first().remove();
				});
			}

			hideEmptyNavbarLists();

			function highlightActiveNavbarItem(){
				$('nav ul li.active').parents('li').addClass('active');
			}

			highlightActiveNavbarItem();

			function hideEmptyActionMenu(){
				$('ul.dropdown-menu[role=menu]').each(function(){
					if($(this).children('li').length < 1) $(this).remove();
				});
			}

			hideEmptyActionMenu();

			$(window).on('resize', function() {
				var hHeight = $('#header').height();
				var lHeight = $('.login-info').height();
				var wHeight = $(window).height();
				$('#navigation-menu').slimScroll({
					height: (wHeight - (hHeight+lHeight+12))+'px'
				});
			});

            eproject = {
                translate: function(callback, ids, translationParams=[], locale=null){
                    /**
                    * Use Laravel's translation(s) in a callback.
                    * @param {Function} callback The callback that will receive and use the translations.
                    * @param {string|Array string} ids The translation id, or an array of translation ids.
                    * @param {Object|Array Object} translationParams The translation parameters, or an array of translation parameters.
                    * @param {string} locale Optional. The locale for the translation.
                    */
                    $.get("{{ route('translate') }}", {
                        ids: ids,
                        params: translationParams,
                        locale: locale
                    })
                    .done(function(data){
                        if(data.success) callback(data.translation);
                    })
                    .fail(function(data){
                        console.error(data.errorMessage);
                    });
                },
                generateRoutes: function(callback, routes, translationParams=[]){
                    /**
                    * Use Laravel's routes in a callback.
                    * @param {Function} callback The callback that will receive and use the routes.
                    * @param {Object|Array Object} routes The route information. Format = {"route1":[routeParamA, routeParamB], "route2":[routeParamC, routeParamD]}.
                    */
                    $.get("{{ route('routes.generate') }}", {
                        routes: routes
                    })
                    .done(function(data){
                        if(data.success) callback(data.routes);
                    })
                    .fail(function(data){
                        console.error(data.errorMessage);
                    });
                }
            };
			
			$(document).idleTimeout({
				redirectUrl: '{{ route("users.logout") }}',
				idleTimeLimit: 1800, //30 min
				idleCheckHeartbeat: 2,
				customCallback: false,
				activityEvents: 'click keypress scroll wheel mousewheel mousemove',
				enableDialog: true,
				dialogDisplayLimit: 20,
				dialogTitle: 'Session Expiration Warning',
				dialogText: 'Because you have been inactive, your session is about to expire.<br/><br/>Click <strong>OK</strong> to continue, or <strong>Cancel</strong> to logout',
				dialogTimeRemaining: 'Time remaining',
				dialogStayLoggedInButton: 'OK',
				dialogLogOutNowButton: '{{trans("forms.cancel")}}',
				// error message if https://github.com/marcuswestin/store.js not enabled
				errorAlertMessage: 'Please disable "Private Mode", or upgrade to a modern browser. Or perhaps a dependent file missing. Please see: https://github.com/marcuswestin/store.js',
				// server-side session keep-alive timer
				sessionKeepAliveTimer: 600,
				sessionKeepAliveUrl: window.location.href
			});
        });
    </script>

	@section('js')
	@show

	@section('jquery_uploader_js')
	@show

	<script>
	@section('inline-js')
	@show
	</script>

	<script>
	@section('jquery_uploader_js_inline_js')
	@show
	</script>
</body>
</html>
