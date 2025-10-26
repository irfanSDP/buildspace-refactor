@extends('layout.main')

@section('css')
    <style>
		.jarviswidget {
            padding: 0;
        }
        .tabulator .tabulator-tableHolder {
			border: none;
		}
        .color-picker {
            height: 38px;
            padding: 0;
            border: none;
            cursor: pointer;
        }
    </style>
@endsection

@section('breadcrumb')
    <ol class="breadcrumb">
        <li>{{ link_to_route('projects.index', trans('navigation/mainnav.home'), array()) }}</li>
        <li>{{ link_to_route('projects.show', str_limit($project->title, 50), array($project->id)) }}</li>
        <li>{{ link_to_route('projects.e_bidding.index', trans('eBidding.ebidding'), array($project->id)) }}</li>
        <li>{{ trans('eBidding.eBiddingSetup') }}</li>
    </ol>
@endsection

@section('content')
	<div class="row">
		<div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
			<h1 class="page-title txt-color-blueDark">
				<i class="fa fa-gavel"></i> {{ trans('eBiddingZone.setupZone') }}
			</h1>
		</div>

		<div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
			<a href="#" class="btn btn-primary btn-md pull-right header-btn" id="add-zone-btn" data-url="{{ route('projects.e_bidding.zones.store', array($project->id, $eBidding->id)) }}" data-target="#zoneEditorModal" data-toggle="modal">
				<i class="fa fa-plus"></i> {{ trans('eBiddingZone.addZone') }}
			</a>
		</div>
	</div>

	<div class="jarviswidget ">
		<header>
			<h2> {{ trans('eBiddingZone.zones') }} </h2>
		</header>
		<div>
			<div class="widget-body no-padding margin-left-5">
				<div id="setup-zones-table"></div>
                <div class="widget-footer">
                    {{ link_to_route('projects.e_bidding.edit', trans('forms.back'), array($project->id, $eBidding->id), array('class' => 'btn btn-default')) }}
                    {{ link_to_route('projects.e_bidding.assignCommittees', trans('forms.next'), array($project->id), array('id' => 'next-page-btn', 'class' => 'btn btn-primary', 'style' => 'margin-left: 10px;')) }}
                </div>
			</div>
		</div>
	</div>

    @include('e_bidding.setup.zones.setup_modal')
	@include('templates.yesNoModal', [
        'modalId'   => 'yesNoModal',
        'titleId'   => 'yesNoModalTitle',
        'title'     => trans('general.confirmation'),
        'messageId' => 'yesNoModalMessage',
    ])
	@include('templates.warning_modal', [
        'modalId'          => 'warningModal',
        'warningMessageId' => 'txtWarningMessage',
    ])
@endsection

@section('js')
    @include('common.scripts')
	<script>
		$(document).ready(function() {
			var setupZonesTable = null;
            let setupZonesModalId = '#zoneEditorModal';

            function validHexColour(colour) {
                if (! /^#([0-9A-F]{3}){1,2}$/i.test(colour)) {
                    return '#ffffff'; // default colour if invalid
                }
                return colour;
            }

            var actionsFormatter = function(cell, formatterParams, onRendered) {
                var rowData = cell.getRow().getData();

                var editButton = document.createElement('a');
                editButton.id = 'btnEditZone_' + rowData.id;
                editButton.className = 'btn btn-xs btn-warning';
				editButton.innerHTML = '<i class="fa fa-pencil-alt"></i>';
				editButton.style['margin-right'] = '5px';
                editButton.dataset.name = rowData.name;
                editButton.dataset.description = rowData.description;
                editButton.dataset.upper_limit = rowData.upper_limit;
                editButton.dataset.colour = rowData.colour;
                editButton.dataset.url = rowData.route_update;

                var deleteButton = document.createElement('a');
				deleteButton.dataset.toggle = 'tooltip';
				deleteButton.title = "{{ trans('eBiddingZone.deleteZone') }}";
                deleteButton.className = 'btn btn-xs btn-danger';
                deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
                deleteButton.style['margin-right'] = '5px';
				deleteButton.dataset.toggle = 'modal';
				deleteButton.dataset.target = '#yesNoModal';

				deleteButton.addEventListener('click', function(e) {
					e.preventDefault();

					$('#yesNoModalMessage').html("{{ trans('formBuilder.allContentsWillBeDeleted') . ' ' . trans('general.sureToProceed') }}");
					$('[data-action=actionYes]').data('route_delete', rowData.route_delete);
				});

				var container = document.createElement('div');
				container.appendChild(editButton);
                container.appendChild(deleteButton);

				return container;
			}

            var columns = [
                { title:"{{ trans('eBiddingZone.rowNo') }}", formatter:"rownum", width:60, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false },
                { title:"{{ trans('eBiddingZone.name') }}", field:"name", hozAlign:'left', cssClass:"text-middle text-left" },
                { title:"{{ trans('eBiddingZone.description') }}", field:"description", hozAlign:'left', cssClass:"text-middle text-left", headerSort:false },
                { title:"{{ trans('eBiddingZone.upperLimit') }}", field:"upper_limit", width:300, hozAlign:'center', cssClass:"text-center text-middle", formatter:"money", formatterParams: {
                    decimal: '.',
                    thousand: ',',
                    symbol: "{{ $currencyCode }}" + ' ',
                    precision: 2,
                }},
                { title:"{{ trans('eBiddingZone.colour') }}", field:"colour", width:80, hozAlign:'center', cssClass:"text-center text-middle", headerSort:false, formatter:"color" },
                { title:"{{ trans('general.actions') }}", width:100, hozAlign:'center', headerSort:false, cssClass:"text-center text-middle", formatter:actionsFormatter },
            ];

            setupZonesTable = new Tabulator('#setup-zones-table', {
                height: 400,
                columns: columns,
                layout: 'fitColumns',
                movableColumns: true,
                columnHeaderSortMulti: false,
                ajaxConfig: "GET",
                ajaxFiltering: true,
                ajaxURL: "{{ route('projects.e_bidding.zones.list', [$project->id, $eBidding->id]) }}",
                placeholder: "{{ trans('general.noRecordsFound') }}",
                paginationSize: 10,
                pagination: "remote",
            });

			function disableZoneSubmitBtn(disable) {
                $(setupZonesModalId + ' .submit-button').prop('disabled', disable);
            }

            function toggleZoneEditor(show) {
                if (show) {
                    $(setupZonesModalId).modal('show');
                } else {
                    $(setupZonesModalId).modal('hide');
                }
            }

            function submitZoneSetup() {
                let url = $(setupZonesModalId + ' .submit-button').data('url');
                $('#level-zone-name-error').text('');
                $('#input-zone-upper-limit-error').text('');
                $('#input-zone-colour-error').text('');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        name: $('#input-zone-name').val().trim(),
                        description: $('#input-zone-description').val().trim(),
                        upper_limit: $('#input-zone-upper-limit').val().trim(),
                        colour: $('#input-zone-colour').val().trim(),
                        _token: "{{ csrf_token() }}",
                    },
                    success: function (data) {
                        if (data.success) {
                            toggleZoneEditor(false);
                            setupZonesTable.setData();
                        }
                        else {
                            $('#level-zone-name-error').text(data['errors']['name']);
                            $('#input-zone-upper-limit-error').text(data['errors']['upper_limit']);
                            $('#input-zone-colour-error').text(data['errors']['colour']);
                            disableZoneSubmitBtn(false);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // error
                    }
                });
            }

            $(setupZonesModalId).on('shown.bs.modal', function(e) {
                disableZoneSubmitBtn(false);
            });

            $(setupZonesModalId).on('click', '.submit-button', function() {
                disableZoneSubmitBtn(true);
                submitZoneSetup();
            });

			// Create
            $(document).on('click', '#add-zone-btn', function(e) {
				e.preventDefault();

                let t = $(this);

                $(setupZonesModalId + ' .modal-title').text("{{ trans('eBiddingZone.addZone') }}");
                $('#input-zone-name').val('');
                $('#input-zone-description').val('');
                $('#input-zone-upper-limit').val('');
                $('#input-zone-colour').val(validHexColour('#ffffff'));

                $(setupZonesModalId + ' .submit-button').data('url', t.data('url'));

                $('#level-zone-name-error').text('');
                $('#input-zone-upper-limit-error').text('');
                $('#input-zone-colour-error').text('');
            });

			// Edit
			$(document).on('click', '[id^=btnEditZone_]', function(e) {
				e.preventDefault();

                let t = $(this);

				$(setupZonesModalId + ' .modal-title').text("{{ trans('eBiddingZone.editZone') }}");
                $('#input-zone-name').val(t.data('name'));
                $('#input-zone-description').val(t.data('description'));
                $('#input-zone-upper-limit').val(t.data('upper_limit'));
                $('#input-zone-colour').val(validHexColour(t.data('colour')));

                $(setupZonesModalId + ' .submit-button').data('url', t.data('url'));

                $('#level-zone-name-error').text('');
                $('#input-zone-upper-limit-error').text('');
                $('#input-zone-colour-error').text('');

				toggleZoneEditor(true);
			});

            $('#yesNoModal').on('click', '[data-action="actionYes"]', function(e) {
				e.preventDefault();
				e.stopPropagation();

                let url = $(this).data('route_delete');

				$.ajax({
                    url: url,
                    method: 'DELETE',
                    data: {
                        _token: _csrf_token,
                    },
                    success: function (data) {
                        if (data.success) {
							setupZonesTable.setData();
                        }
                        $('#yesNoModal').modal('hide');
                    },
                    error: function (request, status, error) {
                        // error
                    }
                });
            });

            $(document).on('click', '#next-page-btn', function (e) {
                let zoneData = setupZonesTable.getData();

                if (zoneData.length === 0) {
                    e.preventDefault(); // stop the link
                    notifyMsg('error', "{{ trans('eBiddingZone.errorAtLeastOneZoneRequired') }}");
                }
            });
		});
	</script>
@endsection