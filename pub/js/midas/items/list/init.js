$(function() {
    $("table#sortTableExample").tablesorter({
        headers: { 7: { sorter: false } },
        sortList: [[2,1]]
    });
});