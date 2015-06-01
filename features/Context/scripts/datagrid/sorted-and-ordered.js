function (columnLabel) {

    var headers = $('thead tr').find('th:visible');

    var index = 0
    var columnPosition = 0;

    _.each(headers, function(tr) {
        console.log($(tr).text());
       if ($(tr).text().trim() == columnLabel) {
           columnPosition = index;
       }
        index ++;
    });

    var values = [];

    _.each($("body tr"), function (row) {
        var cells = [];
        _.each($(row).find('td:visible'), function (cell) {
            var style = $(cell).css('style');
            if (!style || !style.indexOf('display: none')) {
                if ($(cell).find('span').length) {
                    if ($(cell).find('span').hasClass('label-success')) {
                        cells.push(0);
                    } else {
                        cells.push(1);
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

    return JSON.stringify(values);
}
