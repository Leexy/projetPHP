jQuery(function () {
  'use strict';

  Battleship.actions = [];
  Battleship.previousState = { state: null };
  Battleship.currentState = { state: null };
  Battleship.pollInterval = 2500

  Battleship.registerAction = function registerAction(action) {
    Battleship.actions.push(action);
  };

  function stateChanged(previous, current) {
    for (var property in current) {
      if (current.hasOwnProperty(property) && typeof current[property] !== 'object') {
        if (current[property] !== previous[property]) {
          return true;
        }
      }
    }
    return false;
  }

  Battleship.run = function run() {
    Battleship.api.fetchState(function (game) {
      if (stateChanged(Battleship.currentState, game)) {
        Battleship.previousState = Battleship.currentState;
        Battleship.currentState = game;
        Battleship.handleStateChange();
      }
      if (Battleship.currentState.state == Battleship.gameState.finished) {
        return;
      }
      setTimeout(Battleship.run, Battleship.pollInterval);
    });
  };

  Battleship.handleStateChange = function handleStateChange() {
    console.log('state changed: ', Battleship.previousState, '→', Battleship.currentState);
    Battleship.actions.forEach(function (action) {
      if (
        (
          action.previousGameStates === '*' ||
          action.previousGameStates.indexOf(Battleship.previousState.state) !== -1
        ) &&
        (
          action.currentGameStates === '*' ||
          action.currentGameStates.indexOf(Battleship.currentState.state) !== -1
        )
      ) {
        action.proceed(Battleship.currentState);
      }
    });
  };
});
