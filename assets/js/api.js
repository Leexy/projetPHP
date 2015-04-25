jQuery(function () {
  'use strict';
/* Requetes Ajax */
  var api = window.Battleship.api = {};

  //recupere un etat
  api.fetchState = function fetchState(callback){
    jQuery.ajax({
      success: callback,
      error: function () {
        console.error(arguments);
      },
      type: 'GET',
      url: Battleship.url.state
    });
  };

  //envoi une requete de placement de bateau
  api.placeShip = function postPlaceShip(boat, callback) {
    jQuery.ajax({
      contentType: 'application/json',
      data: JSON.stringify(boat.shipData),
      success: function (result) {
        boat.id = result.id;
        callback(result);
      },
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
  //envoi une requete pour dire que le joueur est pret
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
      success: function (data, status, jqXhr) {
        callback(null, data);
      },
      error: function (jqXhr, jqStatus, httpStatus) {
        callback(httpStatus);
      },
      processData: false,
      type: 'POST',
      url: Battleship.url.hit
    });
  };
});
