jQuery(function () {
  'use strict';

  var Polling = window.Polling = {
    fetch: polling,
    period: 2500//ms
  };
  /* Execute callback once the specify hasResult function returns true,
   fetching data from endpoint periodically */
  function polling(endpoint, hasResult, callback) {
    var pollingArgs = arguments;
    var request = $.ajax(endpoint);
    request.done(function (data) {
      if (hasResult(data)) {
        callback(null, data);
      } else {
        retryWith(pollingArgs);
      }
    });
    request.fail(function () {
      retryWith(pollingArgs);
    });
  }
  /* Retry the polling function every Polling.period ms */
  function retryWith(pollingArgs) {
    setTimeout(function () {
      polling(pollingArgs[0], pollingArgs[1], pollingArgs[2]);
    }, Polling.period);
  }
});
