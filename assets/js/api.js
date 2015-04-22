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
  //envoi une requete de placement de bateau
  api.placeShip = function postPlaceShip(ship, callback) {
    jQuery.ajax({
      contentType: 'application/json',
      data: JSON.stringify(ship),
      success: callback,
      error: function () {
        // api.placeShip(ship, callback);
        console.error(arguments);
      },
      processData: false,
      type: 'POST',
      url: Battleship.url.placeShip
    });
  };
  //envoi des requetes de placement de bateaux en parallele 
  api.placeShips = function placeShips(ships, callback) {
    async.each(ships, function (ship, done) {
      api.placeShip(ship, function () { done() });
    }, callback);
  };

  api.ready = function postReady(callback) {
    jQuery.ajax({
      contentType: 'application/json',
      success: callback,
      error: function () {
        // api.ready(callback);
        console.error(arguments);
      },
      processData: false,
      type: 'POST',
      url: Battleship.url.ready
    });
  };

  // envoi une requete pour le hit
  api.hit = function postHit(hit, callback) {
    jQuery.ajax({
      contentType: 'application/json',
      data: JSON.stringify(hit),
      success: callback,
      error: function () {
        // postHit(hit, callback);
        console.error(arguments);
      },
      processData: false,
      type: 'POST',
      url: Battleship.url.hit
    });
  };
});
