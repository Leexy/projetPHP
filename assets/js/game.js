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
    var canvas = $('#cvsPlayer').attr({width: cw, height: ch});
    drawGrid(canvas,gridWidth,gridHeight,p);
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
    var canvas = $('#cvsEnemy').attr({width: cw, height: ch});
    drawGrid(canvas,gridWidth,gridHeight,p);  
  }
  /* function that draw the grid */
  function drawGrid(cvs,width,height,p){
    var ctx = cvs.get(0).getContext("2d");
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
  initEnemyGrid();
  initPlayerGrid();

});
