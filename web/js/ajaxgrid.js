$.fn.ajaxgrid = function(options) {
    var root = this;
    var table, tbody;
    var headerRow;
    var filters, pagination;
    var request = {
        rows: options.rowsPerPage,
        page: 1
    };

    completeOptions(options);
    createBaseElements();
    sendInitRequest();

    return root;

    function completeOptions(options) {
        if (!options.dataUrl) {
            throw 'Wrong "dataUrl" exception';
        }
        options.sortableColumns = options.sortableColumns || [];
        options.filterableColumns = options.filterableColumns || [];
        options.rowsPerPage = options.rowsPerPage || 5;
    }
    function createBaseElements() {
        filters = createElement('div', root).addClass('filters');
        table = createElement('table', root);
        headerRow = createElement('tr', table);
        tbody = createElement('tbody', table);
        pagination = createElement('div', root).addClass('pagination');
    }
    function sendInitRequest() {
        $.getJSON(options.dataUrl, request, function(json) {
            var columns = Object.getOwnPropertyNames(json.data[0]);
            setHeader(headerRow, columns, json.columns, options.sortableColumns);
            setData(tbody, json.data);
            setPagination(pagination, Math.ceil(json.rows / options.rowsPerPage));
            if (options.filterableColumns.length !== 0) {
                setFilters(filters, options.filterableColumns, json.columns);
            }
        });
    }

    function setHeader(headerRow, columns, translations, sortable) {
        headerRow.empty();
        setHeaderText(headerRow, columns, translations);
        setHeaderSort(headerRow, sortable);
    }
    function setHeaderText(headerRow, columns, translations) {
        for (var i = 0; i < columns.length; i++) {
            if (columns[i] !== 'id') {
                createElement('th', headerRow).text(translations[columns[i]]).attr('id', columns[i]);
            }
        }
        createElement('th', headerRow).attr('id', '_edit');
    }
    function setHeaderSort(headerRow, sortable) {
        sortable.forEach(function (column) {
            headerRow.find('#' + column)
                .addClass('sortable')
                .click(function(){
                    onSortClick(column);
                });
        });
    }

    function setData(tbody, data) {
        tbody.empty();
        for (var i = 0; i <data.length; i++) {
            var row = createElement('tr', tbody);
            setRow(row, data[i]);
        }
    }
    function setRow(row, data) {
        for (var dataCell in data) {
            if (data.hasOwnProperty(dataCell) && dataCell !== 'id') {
                createElement('td', row).text(data[dataCell]);
            }
        }
        var editTd = createElement('td', row);
        var editDiv = createElement('div', editTd).addClass('content-justify');
        createElement('i', editDiv).addClass('glyphicon glyphicon-pencil glow edit').click(function () {
            var win = window.open(Routing.generate(options.edit, {id: data.id}), '_blank');
            win.focus();
        });
        createElement('i', editDiv).addClass('glyphicon glyphicon-remove glow remove').click(function () {
            $.ajax(
                Routing.generate(options.edit, {id: data.id}),
                {
                    type: 'DELETE',
                    success: function () {
                        $.getJSON(options.dataUrl, request, function (json) {
                            setData(tbody, json.data);
                            setPagination(pagination,
                                Math.ceil(json.rows / options.rowsPerPage));
                        });
                    }
                }
            );
        });
    }

    function setPagination(pagination, pages) {
        pagination.empty();
        if (pages > 1) {
            var page = request.page ? request.page : 0;
            for (var i = 0; i < pages; i++) {
                var button = createElement('button', pagination)
                    .text(i + 1)
                    .click(onPageClick.bind(i + 1));
                if (i + 1 === +page) {
                    button.addClass('selected');
                }
            }
        }
    }
    function setFilters(filters, filterable, translations) {
        filters.empty();
        var list = createElement('select', filters);
        createElement('input', filters).attr('type', 'text');
        createElement('button', filters).text(options.filter).click(onFilterClick);
        filterable.forEach(function (column) {
            createElement('option', list).text(translations[column]).attr('value', column);
        });

    }

    function createElement(element, root) {
        return $('<' + element + '>').appendTo(root);
    }

    function onFilterClick() {
        var pattern = $('.filters input').val();
        if (pattern !== '') {
            request.pattern = pattern;
            request.filterbyfield = $('.filters select').val();
            request.page = 1;
        } else {
            delete request.pattern;
            delete request.filterbyfield;
        }
        $.getJSON(options.dataUrl, request, function (json) {
            setData(tbody, json.data);
            setPagination(pagination,
                Math.ceil(json.rows / options.rowsPerPage));
        });
    }
    function onPageClick() {
        if (request.page !== this) {
            request.page = this;
            $.getJSON(options.dataUrl, request, function (json) {
                setData(tbody, json.data);
                setPagination(pagination,
                    Math.ceil(json.rows / options.rowsPerPage));
            });
        }
    }
    function onSortClick(column) {
        if (column === request.sortbyfield) {
            if (request.order === 'asc') {
                root.find('#' + column)
                    .addClass('desc')
                    .removeClass('asc');
                request.order = 'desc';
            } else {
                root.find('#' + column)
                    .removeClass('desc');
                delete request.order;
                delete request.sortbyfield;
            }
        } else {
            root.find('#' + column)
                .addClass('asc');
            root.find('#' + request.sortbyfield)
                .removeClass('asc')
                .removeClass('desc');
            request.sortbyfield = column;
            request.order = 'asc';
        }
        $.getJSON(options.dataUrl, request, function (json) {
            setData(tbody, json.data);
            setPagination(pagination,
                Math.ceil(json.rows / options.rowsPerPage));
        });
    }
};