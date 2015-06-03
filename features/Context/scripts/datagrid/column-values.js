function columnValues(columnLabel) {

    console.log('column label = ' + columnLabel);

    var headers = $('thead tr').find('th:visible');

    var index = 0;
    var found = false;
    var columnPosition = 0;

    _.each(headers, function (tr) {
        if ($(tr).text().trim() == columnLabel) {
            found = true;
            columnPosition = index;
        }
        index++;
    });

    if (!found) {
        getError('Column not found : ' + columnLabel);
    }

    console.log('column position = ' + columnPosition);

    var values = [];

    _.each($("body tr"), function (row) {
        var cells = [];
        _.each($(row).find('td:visible'), function (cell) {
            var style = $(cell).css('style');
            if (!style || !style.indexOf('display: none')) {
                if ($(cell).find('span').length) {
                    if ($(cell).find('span').hasClass('label-success')) {
                        cells.push(1);
                    } else {
                        cells.push(0);
                    }
                } else {
                    cells.push($(cell).text().trim());
                }
            }
        });

        if (cells[columnPosition] !== undefined) {
            values.push(cells[columnPosition]);
        }
    });

    console.log(values);

    return JSON.stringify(values);
}
