jQuery(function () {
  'use strict';

  var POLLING_PERIOD = 2500;//ms

  waitForGameState(GAME_STATE.PLAYING, function (error, game) {
    if (game.play) {
      console.log('This is my turn!');
    } else {
      waitForMyTurn(function (error, game) {
        console.log('This is my turn!');
      });
    }
  });

  function waitForMyTurn(callback) {
    Polling.fetch({ url: GAME_STATE_URL }, function (game) {
      return game.play;
    }, callback);
  }

  function waitForGameState(state, callback) {
    Polling.fetch({ url: GAME_STATE_URL }, function (game) {
      return game.state === state;
    }, callback);
  }
});
