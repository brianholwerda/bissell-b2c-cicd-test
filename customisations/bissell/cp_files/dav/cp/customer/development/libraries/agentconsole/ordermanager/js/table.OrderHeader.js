
(function($){

    $(document).ready(function () {

	var editor = new $.fn.dataTable.Editor( {
		ajax: 'ordermanagerajaxhandler.php',
        table: '#OrderHeader',
        idSrc: 'ordernumber',
        fields: [
            {
                "label": "OrderNumber",
                "name": "ordernumber"
            },
			{
				"label": "Date:",
				"name": "date",
				"type": "datetime",
				"format": "YYYY-MM-DD"
			},
			{
				"label": "Status:",
				"name": "status"
			},
			{
				"label": "Type:",
				"name": "type"
			},
			{
				"label": "Total:",
				"name": "total"
			},
			{
				"label": "PayMethod:",
				"name": "paymethod",
				"type": "select",
				"options": [
					"Credit Card",
					" Check",
					" No Charge"
				]
			},
			{
				"label": "PartNumbers:",
				"name": "partnumbers"
			},
			{
				"label": "CSRName:",
				"name": "csrname"
			},
			{
				"label": "eBayOrderNumber:",
				"name": "ebayordernumber"
			},
			{
				"label": "PaymentType:",
				"name": "paymenttype"
			}
		]
	} );

	var table = $('#OrderHeader').DataTable( {
        ajax: 'ordermanagerajaxhandler.php?action=getorderdata',
        searching: false,
        pagingType: "full_numbers",
        columns: [
            {
                "className": 'details-control',
                "orderable": false,
                "data": null,
                "defaultContent": '',
                "sortable":false
            },
            {
                "data": "ordernumber"
            },
			{
				"data": "date"
			},
			{
				"data": "status"
			},
			{
				"data": "type"
			},
			{
				"data": "total"
			},
			{
				"data": "paymethod"
			},
			{
				"data": "partnumbers"
			},
			{
				"data": "csrname"
			},
			{
				"data": "ebayordernumber"
			},
			{
				"data": "paymenttype"
			}
		],
		select: true,
		lengthChange: false
    });

    $('#OrderHeader tbody').on('click', 'td.details-control', function () {
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            row.child(format(row.data())).show();
            tr.addClass('shown');
        }
    });

    function format(d) {
        // `d` is the original data object for the row
        var childTable = getChildRow(d);
        return childTable;
    }

    function getChildRow(d) {

        var childTable = $('#OrderHeaderDetail').clone();
        var childRowData = $('#childRowData').clone();

        if (d.childTableHTML)
            return d.childTableHTML;

        $.each(d.lines, function (line) {

            var rowHTML = childRowData.prop('outerHTML');
            childTable.removeClass('hidden');

            $.each(d.lines[line], function (key, value) {

                rowHTML = rowHTML.replace('{{lines.' + key + '}}', value);

            });

            rowHTML = $($.parseHTML(rowHTML));
            rowHTML.removeAttr('id');
            
            childTable.find('tbody').append(rowHTML.prop('outerHTML'));

        });

        childTable.find('#childRowData').remove();

        d.childTableHTML = childTable.prop('outerHTML');

        return d.childTableHTML;
    }

    $('#OrderHeader').on('click', 'tbody td:not(:first-child)', function (e) {
        editor.inline(this);
    });
    /*
	new $.fn.dataTable.Buttons( table, [
		{ extend: "create", editor: editor },
		{ extend: "edit",   editor: editor },
		{ extend: "remove", editor: editor }
	] );

	table.buttons().container()
		.appendTo( $('.col-sm-6:eq(0)', table.table().container() ) );
    */
} );

}(jQuery));

