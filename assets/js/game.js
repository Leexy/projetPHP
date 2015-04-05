jQuery(function () {
  'use strict';

  waitForPlayingState(function (error, game) {
    if (game.play) {
      console.log('This is my turn!');
    } else {
      waitForMyTurn(function (error, game) {
        console.log('This is my turn!');
      });
    }
  });

  function waitForMyTurn(fn) {
    $.get(GAME_STATE_URL, function (game) {
      if (!game.play) {
        setTimeout(waitForMyTurn.bind(null, fn), 1000);
      } else {
        fn(null, game);
      }
    });
  }

  function waitForPlayingState(fn) {
    $.get(GAME_STATE_URL, function (game) {
      if (game.state === GAME_STATE.WAITING) {
        setTimeout(waitForPlayingState.bind(null, fn), 1000);
      } else {
        fn(null, game);
      }
    });
  }
});