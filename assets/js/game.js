jQuery(function () {
  'use strict';

  $.get(gameStateUrl, function (data) {
    console.log(data);
  });
});