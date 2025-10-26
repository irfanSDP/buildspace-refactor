'use strict';
// Class definition

var KTDatatableDataLocalDemo = function() {
  // Private functions

  // demo initializer


  var demo = function() {
    var count = 0;
    var dataJSONArray = JSON.parse(
        '[{ ' +
            '"RecordID": 1,' +
            '"Petender": "Jabatan Kerja Raya Negeri Selangor",' +
            '"TenderReferenceNumber": "JKR/SEL/(P)/017(T)/2024",' +
            '"TenderDescription": "(WAJIB MEMPUNYAI SIJIL KONTRAKTOR PENYIAP) MENYIAPKAN BAKI KERJA BALAI BOMBA DAN PENYELAMAT AU3, AMPANG, SELANGOR - TENDER SEMULA",' +
            '"BriefingAttendanceInfo": "Kehadiran Taklimat Diwajibkan",' +
            '"BriefingDateTime": "31 Jul 2024 10:00",' +
            '"BriefingLocation": "SILA BERKUMPUL DI BALAI BOMBA PANDAN INDAH, JALAN PANDAN UTAMA, PANDAN INDAH, 55100 KUALA LUMPUR, WILAYAH PERSEKUTUAN KUALA LUMPUR PADA 31/07/2024 PUKUL 10.00 PAGI",' +
            '"SiteVisitInfo": "Lawatan Tapak",' +
            '"SiteVisitDateTime": "23 Jul 2024 11:00",' +
            '"SiteVisitLocation": "TAPAK CADANGAN BALAI BOMBA HULU KELANG, TAMAN BUKIT JOBINA, 68000 AMPANG JAYA, SELANGOR",' +
            '"Mandatory": "Wajib Hadir",' +
            '"kodBidang": "G6",' +
            '"tarikhJual": "7 Ogos 2024",' +
            '"tarikhTutup": "4 Sep 2024",' +
            '"hargaDokumen": "RM 100.00"' +
        '},{ ' +
            '"RecordID": 2,' +
            '"Petender": "Majlis Bandaraya Petaling Jaya",' +
            '"TenderReferenceNumber": "MBPJ/SH/K/158/2024",' +
            '"TenderDescription": "KERJA-KERJA MEMBAIKPULIH BUMBUNG JEJANTAS PEJALAN KAKI SERTA LAIN-LAIN KERJA BERKAITAN DI JALAN SS 2/24, SS 2 PETALING JAYA",' +
            '"BriefingAttendanceInfo": "Kehadiran Taklimat Diwajibkan",' +
            '"BriefingDateTime": "31 Jul 2024 10:00",' +
            '"BriefingLocation": "SILA BERKUMPUL DI BALAI BOMBA PANDAN INDAH, JALAN PANDAN UTAMA, PANDAN INDAH, 55100 KUALA LUMPUR, WILAYAH PERSEKUTUAN KUALA LUMPUR PADA 31/07/2024 PUKUL 10.00 PAGI",' +
            '"SiteVisitInfo": "Lawatan Tapak",' +
            '"SiteVisitDateTime": "23 Jul 2024 11:00",' +
            '"SiteVisitLocation": "TAPAK CADANGAN BALAI BOMBA HULU KELANG, TAMAN BUKIT JOBINA, 68000 AMPANG JAYA, SELANGOR",' +
            '"Mandatory": "Wajib Hadir",' +
            '"kodBidang": "G5 G3",' +
            '"tarikhJual": "29 Jul 2024",' +
            '"tarikhTutup": "5 Ogos 2024",' +
            '"hargaDokumen": "RM 50.00"' +
        '}]'
    );
  
    var datatable = $('#kt_datatable').KTDatatable({
      // datasource definition
      data: {
        type: 'local',
        source: dataJSONArray,
        pageSize: 10,
      },

      // layout definition
      layout: {
        scroll: false, // enable/disable datatable scroll both horizontal and vertical when needed.
        // height: 450, // datatable's body's fixed height
        footer: false, // display/hide footer
      },

      // column sorting
      sortable: true,

      pagination: true,

      search: {
        input: $('#kt_datatable_search_query'),
        key: 'generalSearch',
      },

      // columns definition
      columns: [{
          field: 'RecordID',
          title: '#',
          sortable: false,
          width: 20,
          type: 'number',
          textAlign: 'center',
          template: function(row, index) {
            return index + 1;
          }
        }, {
          field: 'TenderReferenceNumber',
          title: 'No. Tender',
        }, {
          field: 'kodBidang',
          title: 'Kod Bidang',
        }, {
          field: 'TenderDescription',
          title: 'Tajuk',
          width: 300,
          template: function(row) {
            return `
            <a href="/detail" class="fw-bold text-wrap text-justify text-dark-75 text-hover-primary mb-4">
              ${row.TenderDescription}
            </a></br>
            <div class="my-4" id="template-${row.RecordID}" style="display:none;">
              <p class="fw-semibold mb-0">${row.Petender}</p>
              <p class="fw-lighter mb-4">${row.TenderReferenceNumber}</p>
              <div class="d-flex align-items-center bg-light-secondary rounded p-5 mb-2">
                <!--begin::Title-->
                <div class="d-flex flex-column flex-grow-1 mr-2">
                  <a href="#" class="font-weight-bold text-dark-50 font-size-lg mb-1">${row.BriefingAttendanceInfo}</a>
                  <span class="text-muted font-weight-bold">${row.BriefingDateTime}</span>
                </div>
                <!--end::Title-->
                <!--begin::Lable-->
                <i class="fa-regular fa-calendar-check text-dark-50 me-3 py-1"></i>
                <!--end::Lable-->
              </div>
              <div class="d-flex align-items-center bg-light-secondary rounded p-5">
                <!--begin::Title-->
                <div class="d-flex flex-column flex-grow-1 mr-2">
                  <a href="#" class="font-weight-bold text-dark-50 font-size-lg mb-1">${row.SiteVisitInfo}</a>
                  <span class="text-muted font-weight-bold">${row.SiteVisitDateTime}</span>
                </div>
                <!--end::Title-->
                <!--begin::Lable-->
                <i class="fa-solid fa-road text-dark-50 me-3 py-1"></i>
                <!--end::Lable-->
              </div>
            </div>
            <button  class="btn btn-sm btn-light-warning btn-hover-light-warning font-weight-bold toggle-template-btn " data-id="${row.RecordID}">
              <span class="svg-icon" id="arrow-down-${row.RecordID}">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                  <title>Stockholm-icons / Navigation / Angle-double-down</title>
                  <desc>Created with Sketch.</desc>
                  <defs/>
                  <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                      <polygon points="0 0 24 0 24 24 0 24"/>
                      <path d="M8.2928955,3.20710089 C7.90237121,2.8165766 7.90237121,2.18341162 8.2928955,1.79288733 C8.6834198,1.40236304 9.31658478,1.40236304 9.70710907,1.79288733 L15.7071091,7.79288733 C16.085688,8.17146626 16.0989336,8.7810527 15.7371564,9.17571874 L10.2371564,15.1757187 C9.86396402,15.5828377 9.23139665,15.6103407 8.82427766,15.2371482 C8.41715867,14.8639558 8.38965574,14.2313885 8.76284815,13.8242695 L13.6158645,8.53006986 L8.2928955,3.20710089 Z" fill="#000000" fill-rule="nonzero" transform="translate(12.000003, 8.499997) scale(-1, -1) rotate(-90.000000) translate(-12.000003, -8.499997) "/>
                      <path d="M6.70710678,19.2071045 C6.31658249,19.5976288 5.68341751,19.5976288 5.29289322,19.2071045 C4.90236893,18.8165802 4.90236893,18.1834152 5.29289322,17.7928909 L11.2928932,11.7928909 C11.6714722,11.414312 12.2810586,11.4010664 12.6757246,11.7628436 L18.6757246,17.2628436 C19.0828436,17.636036 19.1103465,18.2686034 18.7371541,18.6757223 C18.3639617,19.0828413 17.7313944,19.1103443 17.3242754,18.7371519 L12.0300757,13.8841355 L6.70710678,19.2071045 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(12.000003, 15.499997) scale(-1, -1) rotate(-360.000000) translate(-12.000003, -15.499997) "/>
                  </g>
                </svg> Detail
              </span>
              <span class="svg-icon" id="arrow-up-${row.RecordID}" style="display:none;">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                    <title>Stockholm-icons / Navigation / Angle-double-up</title>
                    <desc>Created with Sketch.</desc>
                    <defs/>
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <polygon points="0 0 24 0 24 24 0 24"/>
                        <path d="M8.2928955,10.2071068 C7.90237121,9.81658249 7.90237121,9.18341751 8.2928955,8.79289322 C8.6834198,8.40236893 9.31658478,8.40236893 9.70710907,8.79289322 L15.7071091,14.7928932 C16.085688,15.1714722 16.0989336,15.7810586 15.7371564,16.1757246 L10.2371564,22.1757246 C9.86396402,22.5828436 9.23139665,22.6103465 8.82427766,22.2371541 C8.41715867,21.8639617 8.38965574,21.2313944 8.76284815,20.8242754 L13.6158645,15.5300757 L8.2928955,10.2071068 Z" fill="#000000" fill-rule="nonzero" transform="translate(12.000003, 15.500003) scale(-1, 1) rotate(-90.000000) translate(-12.000003, -15.500003) "/>
                        <path d="M6.70710678,12.2071104 C6.31658249,12.5976347 5.68341751,12.5976347 5.29289322,12.2071104 C4.90236893,11.8165861 4.90236893,11.1834211 5.29289322,10.7928968 L11.2928932,4.79289682 C11.6714722,4.41431789 12.2810586,4.40107226 12.6757246,4.76284946 L18.6757246,10.2628495 C19.0828436,10.6360419 19.1103465,11.2686092 18.7371541,11.6757282 C18.3639617,12.0828472 17.7313944,12.1103502 17.3242754,11.7371577 L12.0300757,6.88414142 L6.70710678,12.2071104 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(12.000003, 8.500003) scale(-1, 1) rotate(-360.000000) translate(-12.000003, -8.500003) "/>
                    </g>
                </svg> Detail
              </span>
            </button>
            `;
          }
        }, {
          field: 'tarikhJual',
          title: 'Tarikh Jual',
          type: 'date',
          format: 'MM/DD/YYYY',
        }, {
          field: 'tarikhTutup',
          title: 'Tarikh Tutup',
          type: 'date',
          format: 'MM/DD/YYYY',
        }, {
          field: 'hargaDokumen',
          title: 'Harga Dokumen',
        }],
    });

    // jQuery event handler for the button click
    $(document).on('click', '.toggle-template-btn', function() {
      var id = $(this).data('id');
      $('#template-' + id).toggle();

      if ($('#arrow-down-' + id).is(':visible')) {
        $('#arrow-down-' + id).hide();
        $('#arrow-up-' + id).show();
      } else {
        $('#arrow-down-' + id).show();
        $('#arrow-up-' + id).hide();
      }
    });

    $('#kt_datatable_search_gred').on('change', function() {
      datatable.search($(this).val().toLowerCase(), 'kodBidang');
    });

    $('#kt_datatable_search_closing').on('change', function() {
      datatable.search($(this).val().toLowerCase(), 'tarikhTutup');
    });

    $('#kt_datatable_search_gred, #kt_datatable_search_closing').selectpicker();
  };

  return {
    // Public functions
    init: function() {
      // init dmeo
      demo();
    },
  };
}();

jQuery(document).ready(function() {
  KTDatatableDataLocalDemo.init();
});
