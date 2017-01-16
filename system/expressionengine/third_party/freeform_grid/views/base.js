<script type="text/javascript">
;(function($) {
    var Solspace = window.Solspace = window.Solspace || {};
    Solspace.Grid = {};

    Solspace.Grid.settings = {
        init: function(options) {
            $.extend(this, options);
            if (this.headings.length == 0) {
                var code = this.tmpl.format('');
                this.el.append(code);
            } else {
                var my = this;
                $(my.headings).each(function(i,n) {
                    var code = my.tmpl.format(n.heading);
                    my.el.append(code);
                });
            }
            this.el.append(this.add_tmpl);
            this.update();
        },

        update: function() {
            var my = this;
            this.fields   = this.el.find('input');
            this.headings = [];
            $(this.fields).each(function(i,field) {
                my.headings.push({heading: $(field).val()});
            });
            this.master_field.val(JSON.stringify(my.headings));
        }
    }

    Solspace.Grid.display = {
        init: function(options) {
            $.extend(this, options);
            if (!this.vertical_fields) {
	            this.row_base = $('<tr class="freeform-grid-row"></tr>');
	            var row_code = $('<tr class="freeform-grid-heading"></tr>');;
	                my = this;
	            // Build our headings
	            row_code.append($('<th></th>'));
	            $(this.headings).each(function(i,n) {
	                row_code.append(my.heading_tmpl.format(n.heading));
	            });
	            this.el.append(row_code);
		     } else {
		     	this.row_base = $('<div class="freeform-grid-row"></div>');
		     }
            // Build our rows
            this.build_rows();
        },

        update: function() {
            var table_rows  = this.el.find('.freeform-grid-row'),
                my          = this;
            this.num_rows   = table_rows.length;
            this.rows       = [];
            table_rows.each(function(i, row) {
                var data = [],
                    cells = $(row).find('input');
                cells.each(function(i, cell) {
                    data.push($(cell).val());
                });
                my.rows.push(data);
                $(row).find('.freeform-grid-row-number').text(i+1);
            });
            this.master_field.val(JSON.stringify(this.rows));
        },

        build_rows: function(rows) {
            var my          = this,
                data        = (rows == undefined) ? this.rows : rows,
                num_rows    = this.el.find('.freeform-grid-row').length;

            $(data).each(function(i,row) {
                row_code = my.row_base.clone();
                var title_code =  my.title_tmpl;
                row_code.append(title_code);
                var del_code = (num_rows+1 <= my.minrows) ? '<td></td>' : my.delete_row_tmpl.format('');
                row_code.append(del_code);
                $(my.headings).each(function(j,obj) {
                    var val = (row[j] != undefined) ? row[j] : '';
                    if (this.vertical_fields) {
						cell_code = my.cell_tmpl.format(obj.heading,obj.heading.toLowerCase().replace(/[^a-zA-Z0-9]/gi, '_'), val, (this.validate_cells ? 'required' : ''));
                    } else {
                    	cell_code = my.cell_tmpl.format(encodeURIComponent(obj.heading.toLowerCase().replace(/[^a-zA-Z0-9]/gi, '_')), val, (this.validate_cells ? 'required' : ''));	
                    }
                    
                    row_code.append(cell_code);
                }.bind(this));
                my.el.append(row_code);
            }.bind(this));

            this.update();
        }
    }

    String.prototype.format = function() {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function(match, number) {
            return typeof args[number] != 'undefined' ? args[number] : match;
        });
    };
})(jQuery);
</script>
