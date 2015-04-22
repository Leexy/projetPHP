jQuery(function () {
  'use strict';

  var api = window.Battleship.api = {};

  /* Execute le callback quand l'état correspond à celui qui a été spécifié */
  api.waitForGameState = function waitForGameState(state, callback) {
    Polling.fetch({ url: Battleship.url.state }, function (game) {
      return game.state === state;
    }, callback);
  };

  /* Execute le callback quand c'est le tour du joueur */
  api.waitForMyTurn = function waitForMyTurn(callback) {
    Polling.fetch({ url: Battleship.url.state }, function (game) {
      return game.play;
    }, callback);
  };

  api.hit = function postHit(hit, callback) {
    jQuery.ajax({
      contentType: 'application/json',
      data: JSON.stringify(hit),
      success: callback,
      error: function () {
        postHit(hit, callback);
      },
      processData: false,
      type: 'POST',
      url: Battleship.url.hit
    });
  };
});
