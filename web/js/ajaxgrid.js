!function ($) {

    var Grid = function (element, options) {
        this.$element = $(element)
        this.options = $.extend({}, $.fn.grid.defaults, options)
        this.ajaxUrl = this.options.ajaxUrl || this.ajaxUrl
        this.limit = this.options.limit || this.limit
        this.sortIndex = ''
        this.sortOrder = 'ASC'
        this.page = 1
        this.totalRows = 0
        this.totalPages = 0
        this.listen()
        this.ajax()
    }

    Grid.prototype = {

        constructor: Grid

       , ajax: function () {

           var filters = this.$element.find('form').serializeArray(),
               tbody = this.$element.find('table').find('tbody.row-result'),
               emptyTbody = this.$element.find('table').find('tbody.row-empty'),
               thisClass = this;

           this.page = this.$element.find('#pagination #pagination-page').val();
           this.limit = this.$element.find('#pagination #pagination-limit').val();

           $.ajax({
               url:this.ajaxUrl,
               type: 'get',
               data: {
                   page: this.page,
                   limit: this.limit,
                   filters: filters,
                   sort: this.sortIndex,
                   sort_order: this.sortOrder,
               }
           })

           dataType: 'JSON',
            
           thisClass.page = data.page,
           thisClass.limit = data_page.limit,
           thisClass.totalRows = data.row_count,
           thisClass.totalPages = data.page_count,

           thisClass.paginationProcess()

           var html = ''

           if (data.rows.length > 0) {
               emptyTbody.hide()
               $.each(data.rows, function (i, item) {
                   html += '<tr>'
                   $.each(item, function (i, value) {
                       if (value == null) {
                           value = ''
                       }

                       html += '<td>' + value + '</td>'
                   })
                   html += '</tr>'
               })
           } else {
               emptyTbody.show()
           }

           tbody.html(html)
       },

        error:function (error) {
            thisClass.gridUnlock()

            emptyTbody.show()
            tbody.html('')

            alert('Error: ' + error.statusText)
        }
    }

    return this
}

      listen:function All() {
        this.$element.find('form').on('submit', $.proxy(this.submit, this))
        this.$element.find('select').on('change', $.proxy(this.ajax, this))

        this.$element.find('#refresh-button').on('click', $.proxy(this.ajax, this))
        this.$element.find('#refresh-filters-button').on('click', $.proxy(this.refreshFilters, this))
        this.$element.find('#row-filters-label th').on('click', $.proxy(this.processOrder, this))

        this.$element.find('#pagination-back-button').on('click', $.proxy(this.paginationBack, this))
        this.$element.find('#pagination-forward-button').on('click', $.proxy(this.paginationForward, this))

    return this
}

        submit:function Submit() {
            this.ajax()

            return false
        }

        refreshFilters:function Refresh() {

            $.each(this.$element.find('form'), function (i, form) {
                form.reset()
            })

            $.each(this.$element.find('.date-input'), function (i, input) {
                $(input).removeAttr('value')
            })

            this.ajax()

            return this
        }

        gridLock:function Lock() {
            this.$element.find('input, select, textarea, button').attr('disabled', true)
            this.$element.css({opacity:0.5});

            return this
        }

        gridUnlock:function Unlock() {
            this.$element.find('input, select, textarea, button').attr('disabled', false)
            this.$element.css({opacity:1});

            return this
        }

        processOrder:function Order(event) {

            var element = $(event.target)
            sortIndex = element.data('index')

            if (!sortIndex) {
                return false
            }

            if (this.sortIndex == sortIndex) {
                if (this.sortOrder == 'DESC') {
                    this.sortOrder = 'ASC'
                } else {
                    this.sortOrder = 'DESC'
                }
            } else {
                this.sortOrder = 'ASC'
                this.sortIndex = sortIndex
            }

            this.processOrderIcon(element)

            this.ajax()

            return this
        }

           paginationProcess: function Pagination() {

               this.$element.find('#pagination #pagination-back-button').attr('disabled', false)
               this.$element.find('#pagination #pagination-forward-button').attr('disabled', false)

               if (this.page <= 1) {
                   this.$element.find('#pagination #pagination-back-button').attr('disabled', true)
               }

               if (this.page >= this.totalPages) {
                   this.$element.find('#pagination #pagination-forward-button').attr('disabled', true)
               }

               this.$element.find('#pagination #pagination-page').val(this.page)
               this.$element.find('#pagination #pagination-total-pages').html(this.totalPages)
               this.$element.find('#pagination #pagination-total').html(this.totalRows)
               this.$element.find('#pagination #pagination-limit').val(this.limit)

               this.$element.find('#pagination input').attr('disabled', false)

               return this
           }

           paginationBack: function PaginationA() {
               this.page--
               this.$element.find('#pagination #pagination-page').val(this.page)

               this.ajax()

               return this
           }

           paginationForward: function PaginationB() {
               this.page++
               this.$element.find('#pagination #pagination-page').val(this.page)

               this.ajax()

               return this
           }

       