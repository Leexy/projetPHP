jQuery(function () {
  'use strict';

  var Polling = window.Polling = {
    fetch: polling,
    period: 2500//ms
  };

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

  function retryWith(pollingArgs) {
    setTimeout(function () {
      polling(pollingArgs[0], pollingArgs[1], pollingArgs[2]);
    }, Polling.period);
  }
});
