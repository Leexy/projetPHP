jQuery(function () {
  'use strict';

  var POLLING_PERIOD = 2500;//ms
  var ctxPlayer = document.getElementById("cvsPlayer").getContext("2d");
  var ctxEnemy = document.getElementById("cvsEnemy").getContext("2d");
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
  /* Execute callback once it's the player's turn */
  function waitForMyTurn(callback) {
    Polling.fetch({ url: GAME_STATE_URL }, function (game) {
      return game.play;
    }, callback);
  }
  /* Execute callback once the state matches the specified one */
  function waitForGameState(state, callback) {
    Polling.fetch({ url: GAME_STATE_URL }, function (game) {
      return game.state === state;
    }, callback);
  }
  
  /* init player's canvas */
  function initPlayerGrid(){
    //grid width and height
    var gridWidth = 400;
    var gridHeight = 400;
    //padding around grid
    var p = 10;
    //size of canvas
    var cw = gridWidth + (p*2) + 1;
    var ch = gridHeight + (p*2) + 201;
    $('#cvsPlayer').attr("width", cw);
    $('#cvsPlayer').attr("height", ch);
    drawGrid(ctxPlayer,gridWidth,gridHeight,p);
    drawBoats();
  }
  /* init enemy's canvas*/
  function initEnemyGrid(){
    //grid width and height
    var gridWidth = 400;
    var gridHeight = 400;
    //padding around grid
    var p = 10;
    //size of canvas
    var cw = gridWidth + (p*2) + 1;
    var ch = gridHeight + (p*2) + 1;
    $('#cvsEnemy').attr("width", cw);
    $('#cvsEnemy').attr("height", ch);
    drawGrid(ctxEnemy,gridWidth,gridHeight,p);
  }
  /* function that draw the grid */
  function drawGrid(ctx,width,height,p){
    var arrayCoordX = ["a","b","c","d","e","f","g","h","i","j"]
    var arrayCoordY = ["1","2","3","4","5","6","7","8","9","10"]
    var i=0;
    for (var x = 0; x <= width; x += 40) {
      if(i<arrayCoordX.length){ // draw the letter coord
        ctx.fillText(arrayCoordX[i], x+p+20, p)
        i++;
      }
        //draw the vertical lines
        ctx.moveTo(0.5 + x + p, p);
        ctx.lineTo(0.5 + x + p, height + p);
      }
    var j=0;
    for (var x = 0; x <= height; x += 40) {
        if(j<arrayCoordY.length){// draw the number coord
          ctx.fillText(arrayCoordY[j], 0, x+p+20);
          j++;
        }
        //draw the horizontal lines
        ctx.moveTo(p, 0.5 + x + p);
        ctx.lineTo(width + p, 0.5 + x + p);
    }
    ctx.strokeStyle = "black";
    ctx.stroke();
  }
  /* function that draw all the boats */
  function drawBoats(){
    for (var i=0; i<boats.length; i++)
    {
      var b = boats[i];
      drawBoat(b);
    }
  }
  /* function that draw specific boat */
  function drawBoat(boat){
    ctxPlayer.fillStyle = "rgb(48,48,48)";
    ctxPlayer.fillRect(boat.x, boat.y,boat.width,boat.height);
  }

  function onUserAction(x,y){
    console.log(x,y);
  }
  
  function onMouseClick(x,y){
    console.log(x,y);
  }

  //init both grids
  initEnemyGrid();
  initPlayerGrid();
  // called every time the mouse is moved
  $('#cvsPlayer').mousemove( function (e) {
    var targetOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.clientX-targetOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.clientY-targetOffset.top : e.offsetY;
    onUserAction(x,y);
  });
  //called when mouse down
  $('#cvsPlayer').mousedown( function (e) {
    var targetOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.clientX-targetOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.clientY-targetOffset.top : e.offsetY;
    if( e.which == 1 ){
      onMouseClick(x,y);
    }
  });
});
