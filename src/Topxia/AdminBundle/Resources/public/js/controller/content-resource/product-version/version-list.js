define(function(require, exports, module) {

  require("jquery.bootstrap-datetimepicker");

  exports.run = function() {
    var $element = $('.js-table-container');

    require('../../../util/short-long-text')($element);

    $("#startDate").datetimepicker({
      autoclose: true
    }).on('changeDate', function() {
      $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));
    });

    $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));

    $("#endDate").datetimepicker({
      autoclose: true
    }).on('changeDate', function() {
      $("#startDate").datetimepicker('setEndDate', $("#endDate").val().substring(0, 16));
    });

    $("#endDate").datetimepicker('setStartDate', $("#startDate").val().substring(0, 16));

  };
});