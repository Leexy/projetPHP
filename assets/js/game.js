jQuery(function () {
  'use strict';

  Polling.period = 100000;//ms
  var ctxPlayer = document.getElementById("cvsPlayer").getContext("2d");
  var ctxEnemy = document.getElementById("cvsEnemy").getContext("2d");
  //hauteur et largeur de la grille
  var gridWidth = 400;
  var gridHeight = 400;
  //padding autour de la grille
  var p = 10;
  //taille du canvas du player
  var cw = gridWidth + (p*2) + 1;
  var ch = gridHeight + (p*2) + 201;
  var draggingBoat = null;
  var boats = [
    {
      name: "submarine",
      x: 5,
      y: 420,
      width: 79,
      height: 39,
    },
    {
      name: "destroyer",
      x: 5,
      y: 470,
      width: 119,
      height: 39,
    },
    {
      name: "destroyer",
      x: 135,
      y: 470,
      width: 119,
      height: 39,
    },
    {
      name: "cruiser",
      x: 5,
      y: 520,
      width: 159,
      height: 39,
    },
    {
      name: "battleship",
      x: 5,
      y: 570,
      width: 199,
      height: 39,
    },
  ];

  waitForGameState(GAME_STATE.PLAYING, function (error, game) {
    if (game.play) {
      console.log('This is my turn!');
    } else {
      waitForMyTurn(function (error, game) {
        console.log('This is my turn!');
      });
    }
  });
  /* Execute le callback quand c'est le tour du joueur */
  function waitForMyTurn(callback) {
    Polling.fetch({ url: GAME_STATE_URL }, function (game) {
      return game.play;
    }, callback);
  }
  /* Execute le callback quand l'etat correspond a celui qui a ete specifie */
  function waitForGameState(state, callback) {
    Polling.fetch({ url: GAME_STATE_URL }, function (game) {
      return game.state === state;
    }, callback);
  }
  
  /* initialise le canvas de la grille du joueur */
  function initPlayerGrid(){
    $('#cvsPlayer').attr("width", cw);
    $('#cvsPlayer').attr("height", ch);
    drawGrid(ctxPlayer,cw,ch,p);
    drawBoats();
  }
  /* initialise le canvas de la grille ennemie */
  function initEnemyGrid(){
    //taille du canvas
    var cw = gridWidth + (p*2) + 1;
    var ch = gridHeight + (p*2) + 1;
    $('#cvsEnemy').attr("width", cw);
    $('#cvsEnemy').attr("height", ch);
    drawGrid(ctxEnemy,gridWidth,gridHeight,p);
  }
  /* Dessine la grille */
  function drawGrid(ctx,width,height,p){
    // clear the canvas
    ctx.fillStyle="white";
    ctx.fillRect(0, 0, width, height);
    var arrayCoordX = ["a","b","c","d","e","f","g","h","i","j"]
    var arrayCoordY = ["1","2","3","4","5","6","7","8","9","10"]
    var i=0;
    for (var x = 0; x <= gridWidth; x += 40){
      if(i<arrayCoordX.length){ // dessine les lettres de la grille
        ctx.fillText(arrayCoordX[i], x+p+20, p)
        i++;
      }
        //dessine les lignes verticales de la grille
        ctx.moveTo(0.5 + x + p, p);
        ctx.lineTo(0.5 + x + p, gridHeight + p);
      }
    var j=0;
    for (var x = 0; x <= gridHeight; x += 40){
        if(j<arrayCoordY.length){// dessine les numeros de grille
          ctx.fillText(arrayCoordY[j], 0, x+p+20);
          j++;
        }
        //dessine les lignes horizontales de la grille
        ctx.moveTo(p, 0.5 + x + p);
        ctx.lineTo(gridWidth + p, 0.5 + x + p);
    }
    ctx.strokeStyle = "black";
    ctx.stroke();
  }
  /* dessine tous les bateaux */
  function drawBoats(){
    for (var i=0; i<boats.length; i++){
      var b = boats[i];
      drawBoat(b);
    }
  }
  /* dessine un bateau specifique */
  function drawBoat(boat){
    ctxPlayer.fillStyle = "rgb(48,48,48)";
    ctxPlayer.fillRect(boat.x, boat.y,boat.width,boat.height);
  }

  function onUserAction(x,y){
    if(draggingBoat){
      draggingBoat.x = x;
      draggingBoat.y = y;
    }
    drawGrid(ctxPlayer,cw,ch,p);
    drawBoats();
  }
  
  function onMouseClick(x,y){
    boats.forEach(function(boat) {
        if(x > boat.x && x < (boat.x + boat.width) && y > boat.y && y < (boat.y +boat.height)){
            draggingBoat = boat;
        }
    });
  }

  function releaseBoat(){
    if (draggingBoat) {
      draggingBoat = null;
      drawGrid(ctxPlayer,gridWidth,gridHeight,p);
      drawBoats();
    }
  }
  //initialise les deux grilles de jeu
  initEnemyGrid();
  initPlayerGrid();
  //appel a chaque fois que la souris bouge
  $('#cvsPlayer').mousemove( function (e) {
    var targetOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.clientX-targetOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.clientY-targetOffset.top : e.offsetY;
    onUserAction(x,y);
  });
  //appel au clic gauche
  $('#cvsPlayer').mousedown( function (e) {
    var targetOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.clientX-targetOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.clientY-targetOffset.top : e.offsetY;
    if( e.which == 1 ){
      onMouseClick(x,y);
    }
  });
  //relache le bateau
  $('#cvsPlayer').mouseup(releaseBoat);
});
