jQuery(function () {
  'use strict';

  Battleship.actions = [];
  Battleship.previousState = { state: null };
  Battleship.currentState = { state: null };
  Battleship.pollInterval = 2500
  //permet d'enregistrer une action a effectuer quand 
  //l'etat du jeu (cote serveur) evolue
  Battleship.registerAction = function registerAction(action) {
    Battleship.actions.push(action);
  };
  //verifie si l'etat du jeu a change
  function stateChanged(previous, current) {
    // TAG:STUCK Workaround for "game is stuck and I need to reload" issue
    if (current.state === Battleship.gameState.playing) {
      return true;
    }
    // ENDTAG
    for (var property in current) {
      if (current.hasOwnProperty(property) && typeof current[property] !== 'object') {
        if (current[property] !== previous[property]) {
          return true;
        }
      }
    }
    return false;
  }
  // polling
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
  //appel les actions enregistrees (si necessaire) lors d'un changement d'etat
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
