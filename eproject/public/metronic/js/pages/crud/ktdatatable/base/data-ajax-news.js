/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};
/*!****************************************************************!*\
  !*** news ***!
  \****************************************************************/

// Class definition

var KTDatatableRemoteAjaxDemo = function() {
    // Private functions
    // basic demo
    var demo = function() {
        var count = 0;
        var datatable = $('#kt_datatable_news').KTDatatable({
            // datasource definition
            data: {
                type: 'remote',
                source: {
                    read: {
                        url:"list-news-ajax",
                        method: 'GET',
                        map: function(raw) {
                        var row = raw.data || raw;
                        console.log('Mapped row:', row);
                        return row;
                        },
                    },
                },
                pageSize: 10,
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true,
            },

            // layout definition
            layout: {
                scroll: false,
                footer: false,
            },

            // column sorting
            sortable: true,

            pagination: true,

            search: {
                input: $('#kt_datatable_search_query'),
                key: 'generalSearch'
            },

            // columns definition
            columns: [{
                field: 'id',
                title: '#',
                sortable: false,
                width: 20,
                type: 'number',
                textAlign: 'center',
                template: function(row, index) {
                  return index + 1;
                }
              }, {
                field: 'department',
                title: 'Jabatan',
              },
              {
                field: 'description',
                title: 'Berita',
                width: 250,
                template: function(row){
                  return `<a href="detail-news/${row.id}" class="text-dark-75 text-hover-primary">${row.description}</a>`
                }
              }, {
                field: 'created_at',
                title: 'Tarikh',
                  template: function(row) {
                    return new Date(row.created_at.date).toLocaleDateString('en-GB');
                },
              }, {
                field: 'status',
                title: 'Status',
              }],

        });
    };

    return {
        // public functions
        init: function() {
            demo();
        },
    };
}();

jQuery(document).ready(function() {
    KTDatatableRemoteAjaxDemo.init();
});

/******/ })()
;
//# sourceMappingURL=data-ajax.js.map