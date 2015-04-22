jQuery(function () {
  'use strict';
  $('body').on('contextmenu', 'canvas', function (){ return false; }); // desactive le clic droit sur le canvas
  Polling.period = 100000;//ms
  var ctxPlayer = document.getElementById("cvsPlayer").getContext("2d");
  var ctxEnemy = document.getElementById("cvsEnemy").getContext("2d");
  //hauteur et largeur de la grille
  var gridWidth = 400;
  var gridHeight = 400;
  //padding autour de la grille
  var p = 10;
  //taille d'une case
  var squareSize = 40;
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
      size: 2,
      orientation: "horizontal",
    },
    {
      name: "destroyer",
      x: 5,
      y: 470,
      width: 119,
      height: 39,
      size: 3,
      orientation: "horizontal",
    },
    {
      name: "destroyer",
      x: 135,
      y: 470,
      width: 119,
      height: 39,
      size: 3,
      orientation: "horizontal",
    },
    {
      name: "cruiser",
      x: 5,
      y: 520,
      width: 159,
      height: 39,
      size: 4,
      orientation: "horizontal",
    },
    {
      name: "battleship",
      x: 5,
      y: 570,
      width: 199,
      height: 39,
      size: 5,
      orientation: "horizontal",
    },
  ];

  /* initialise le canvas de la grille du joueur */
  function initPlayerGrid() {
    $('#cvsPlayer').attr("width", cw);
    $('#cvsPlayer').attr("height", ch);
    drawGrid(ctxPlayer,cw,ch,p);
    drawBoats();
  }
  /* initialise le canvas de la grille ennemie */
  function initEnemyGrid() {
    //taille du canvas
    var cw = gridWidth + (p*2) + 1;
    var ch = gridHeight + (p*2) + 1;
    $('#cvsEnemy').attr("width", cw);
    $('#cvsEnemy').attr("height", ch);
    drawGrid(ctxEnemy,gridWidth,gridHeight,p);
  }
  /* Dessine la grille */
  function drawGrid(ctx,width,height,p) {
    // clear the canvas
    ctx.fillStyle="white";
    ctx.fillRect(0, 0, width, height);
    var arrayCoordX = ["a","b","c","d","e","f","g","h","i","j"]
    var arrayCoordY = ["1","2","3","4","5","6","7","8","9","10"]
    var i=0;
    for (var x = 0; x <= gridWidth; x += 40){
        //dessine les lignes verticales de la grille
        ctx.moveTo(0.5 + x + p, p);
        ctx.lineTo(0.5 + x + p, gridHeight + p);
      }
    var j=0;
    for (var x = 0; x <= gridHeight; x += 40){
        //dessine les lignes horizontales de la grille
        ctx.moveTo(p, 0.5 + x + p);
        ctx.lineTo(gridWidth + p, 0.5 + x + p);
    }
    ctx.strokeStyle = "black";
    ctx.stroke();
  }
  /* dessine tous les bateaux */
  function drawBoats() {
    for (var i=0; i<boats.length; i++){
      var b = boats[i];
      drawBoat(b);
    }
  }
  /* dessine un bateau specifique */
  function drawBoat(boat) {
    ctxPlayer.fillStyle = "rgb(48,48,48)";
    if(boat.orientation == "horizontal"){
      ctxPlayer.fillRect(boat.x, boat.y,boat.width,boat.height);
    }
    else if(boat.orientation == "vertical"){
      ctxPlayer.fillRect(boat.x, boat.y,boat.height,boat.width);
    }
  }
  //change les coord du bateau onmousemove
  function onUserAction(x,y) {
    if(draggingBoat){
      draggingBoat.x = x;
      draggingBoat.y = y;
    }
    drawGrid(ctxPlayer,cw,ch,p);
    drawBoats();
  }
  //fonction qui renvoie le bateau clique
  function getPointedBoat(x,y){
    var selectedBoat;
    boats.forEach(function (boat) {
      if(boat.orientation == "horizontal"){
        if(x > boat.x && x < (boat.x + boat.width) && y > boat.y && y < (boat.y + boat.height)){
          selectedBoat = boat;
        }
      }
      else if(boat.orientation == "vertical"){
        if(x > boat.x && x < (boat.x + boat.height) && y > boat.y && y < (boat.y + boat.width)){
          selectedBoat = boat;
        }
      }
    });
    return selectedBoat;
  }
  //place correctement le bateau dans la case
  function placeShipInSquare() {
    if(draggingBoat){
      draggingBoat.gridX = Math.ceil(draggingBoat.x/squareSize);
      draggingBoat.gridY = Math.ceil(draggingBoat.y/squareSize);
      draggingBoat.x = gridToCanvas(draggingBoat.gridX);
      draggingBoat.y = gridToCanvas(draggingBoat.gridY);
    }
    drawGrid(ctxPlayer,cw,ch,p);
    drawBoats();
  }
  //converti les numeros de case en pixel pour le positionnement
  function gridToCanvas(gridPos) {
    return 1 + p + (gridPos - 1) * squareSize;
  }
  //fonction qui transforme le bateau horizontal en vertical
  function changeOrientation(boat){
      boat.orientation = boat.orientation == "vertical"?"horizontal":"vertical";
      drawGrid(ctxPlayer,gridWidth,gridHeight,p);
      drawBoats();
  }
  //fonction appele quand la souris est relachee
  function releaseBoat() {
    if (draggingBoat) {
      placeShipInSquare();
      draggingBoat = null;
      drawGrid(ctxPlayer,gridWidth,gridHeight,p);
      drawBoats();
    }
  }
  //verifie que tous les bateaux sont bien postionnes dans la grille
  function boatInGrid(boat){
    if(boat.orientation == "horizontal"){
      if((boat.x + boat.width-10) <= gridWidth && (boat.y + boat.height-10) <= gridHeight){
        return true;
      }
      else{
        return false;
      }
    }
    else if(boat.orientation == "vertical"){
      if((boat.x + boat.height-10) <= gridWidth  && (boat.y + boat.width-10) <= gridHeight){
        return true;
      }
      else{
        return false;
      }
    }
  }
  //initialise les deux grilles de jeu
  initEnemyGrid();
  initPlayerGrid();
  //onclick sur le bouton Ready, verifie que tous les bateaux sont bien postionnes dans la grille grace a la fonction boatInGrid
  $('#btnReady').click(function () {
    var positionOk = boats.every(boatInGrid);
    if(!positionOk){
      $( "#alert-msg" ).html( " <div class=\"alert-box error\"><span>error: </span>You should correctly place ALL your boats ! ;).</div>" );
    }
    else{
      $( "#alert-msg" ).html( "<div class=\"alert-box success\"><span>success: </span>You have place all your boats ! Wait till your opponent is ready now ;).</div>" );
      $("#btnReady").disabled = true;
      Battleship.api.placeShips(boats.map(boatToShipModel), function(){
          Battleship.api.waitForGameState(Battleship.state.playing, function (error, game) {
            if (game.play) {
              $( "#alert-msg" ).html( "<div class=\"alert-box success\"><span>success: </span> This is your turn.</div>" );
            } else {
              Battleship.api.waitForMyTurn(function (error, game) {
                $( "#alert-msg" ).html( "<div class=\"alert-box success\"><span>success: </span> This is your turn.</div>" );
              });
            }
          });
      });
    }
  });
  //appel a chaque fois que la souris bouge
  $('#cvsPlayer').mousemove(function (e) {
    var cvsPlayerOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.pageX-cvsPlayerOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.pageY-cvsPlayerOffset.top : e.offsetY;
    onUserAction(x,y);
  });
  //appel au clic gauche (drag & drop du bateau) et clic droit (changement d'orientation)
  $('#cvsPlayer').mousedown(function (e) {
    var cvsPlayerOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.pageX-cvsPlayerOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.pageY-cvsPlayerOffset.top : e.offsetY;
    var pointedBoat = getPointedBoat(x,y);
    if( e.which == 1 ){
      draggingBoat = pointedBoat;
    }
    else if( e.which == 3){
      if(pointedBoat){
        changeOrientation(pointedBoat);
      }
    }
  });
  //relache le bateau
  $('#cvsPlayer').mouseup(releaseBoat);
});
