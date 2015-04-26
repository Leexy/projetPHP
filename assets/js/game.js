jQuery(function () {
  'use strict';
  $('body').on('contextmenu', 'canvas', function (){ return false; }); // desactive le clic droit sur le canvas
  /* DEFINITION DES VARIABLES */
  //definition des son de jeu
  var sound = {
    missedHit: document.getElementById('missed-hit-sound'),
    hitBlast: document.getElementById('hit-blast-sound'),
    shipDestroyed: document.getElementById('ship-destroyed-sound'),
    opponentDestroyed: document.getElementById('opponent-destroyed-sound')
  };
  function playSound(soundName) {
    var audio = sound[soundName];
    audio.currentTime = 0;
    audio.play();
  }
  var enemyCanvas = document.getElementById("cvsEnemy");
  //recuperation des contextes pour les canvas Player et Enemy
  var ctxPlayer = document.getElementById("cvsPlayer").getContext("2d");
  var ctxEnemy = enemyCanvas.getContext("2d");
  //hauteur et largeur de la grille
  var gridWidth = 400;
  var gridHeight = 400;
  //padding autour de la grille
  var p = 10;
  //taille d'une case
  var squareSize = 40;
  //taille du canvas du player (on ajoute la partie ou les bateaux sont dessines)
  var cw = gridWidth + (p*2) + 1;
  var ch = gridHeight + (p*2) + 201;
  var draggingBoat = null;
  var pointedSquare = null;
  var thisIsMyTurn = false;
  var gameState;
  var playerReady;
  var hitsHistory = [];
  var boatNames = {
    2: "submarine",
    3: "destroyer",
    4: "cruiser",
    5: "battleship"
  };
  var lastPlayedHitId;
  var playerHits = [];
  var enemyHits = [];
  var sunkBoats = [];
  var boats = [
    {
      name: "submarine",
      x: 5,
      y: 420,
      width: 79,
      height: 39,
      size: 2,
      orientation: "horizontal"
    },
    {
      name: "destroyer",
      x: 5,
      y: 470,
      width: 119,
      height: 39,
      size: 3,
      orientation: "horizontal"
    },
    {
      name: "destroyer",
      x: 135,
      y: 470,
      width: 119,
      height: 39,
      size: 3,
      orientation: "horizontal"
    },
    {
      name: "cruiser",
      x: 5,
      y: 520,
      width: 159,
      height: 39,
      size: 4,
      orientation: "horizontal"
    },
    {
      name: "battleship",
      x: 5,
      y: 570,
      width: 199,
      height: 39,
      size: 5,
      orientation: "horizontal"
    }
  ];
/************************************************/
  // permet de recuperer le placement des bateaux quand on recharge la page
  // apres avoir valide le positionnement
  Battleship.registerAction({
    previousGameStates: [null],
    currentGameStates: '*',
    proceed: function (game) {
      // TAG:STUCK
      if (game.last_hit) {
        lastPlayedHitId = game.last_hit.id;
      }
      // ENDTAG
      game.player_ships.forEach(function (ship) {
        var boat = getMatchingBoat(ship);
        boat.id = ship.id;
        boat.x = gridToCanvas(+ship.x);
        boat.y = gridToCanvas(+ship.y);
        boat.orientation = ship.orientation.toLowerCase();
      });
      if(game.player_is_ready){
        $("#btnReady").attr('disabled',true);
      }
      drawGrid(ctxPlayer,cw,ch,p);
      drawBoats();
    }
  });
  //permet de recuperer l'etat du jeu et de savoir si le joueur est pret
  Battleship.registerAction({
    previousGameStates: '*',
    currentGameStates: '*',
    proceed: function (game) {
      gameState = game.state;
      playerReady = game.player_is_ready;
    }
  });
  //permet de mettre de a jour le nom de l'ennemi quand celui rejoind la partie
  // et d'afficher le message en consequence
  Battleship.registerAction({
    previousGameStates: [null, Battleship.gameState.waiting],
    currentGameStates: [Battleship.gameState.placing, Battleship.gameState.player1_ready, Battleship.gameState.player2_ready, Battleship.gameState.playing, Battleship.gameState.finished],
    proceed: function (game) {
      $('#opponent-name').text(game.opponentName);
      $('#game').removeClass('no-opponent').addClass('has-opponent');
    }
  });
  //"disable" le canvas Enemy quand le jeu et fini
  //et d'afficher un message avec le nom du gagnant
  Battleship.registerAction({
    previousGameStates: '*',
    currentGameStates: [Battleship.gameState.finished],
    proceed: function (game) {
      $('#cvsEnemy').addClass('disableCanvas');
      $( "#alert-msg" ).html( "<div class=\"alert-box success\">The game is finished, congrats to <strong>" + game.winner + "</strong>!</div>" );
    }
  });
  //permet de jouer les sons des hits, des hits manques
  //et des bateaux coules pendant le jeu
  Battleship.registerAction({
    previousGameStates: [Battleship.gameState.playing],
    currentGameStates: [Battleship.gameState.playing],
    proceed: function (game) {
      if (game.last_hit) {
        // TAG:STUCK
        if (game.last_hit.id === lastPlayedHitId) {
          return;
        }
        // ENDTAG
        if (game.last_hit.destroyed == 1) {
          playSound('shipDestroyed');
        } else if (game.last_hit.success == 1) {
          playSound('hitBlast');
        } else {
          playSound('missedHit');
        }
        // TAG:STUCK
        lastPlayedHitId = game.last_hit.id;
        // ENDTAG
      }
    }
  });
  //permet de jouer un son quand le dernier hit detruit le dernier bateau
  //de la grille
  Battleship.registerAction({
    previousGameStates: [Battleship.gameState.playing],
    currentGameStates: [Battleship.gameState.finished],
    proceed: function () {
      playSound('opponentDestroyed');
    }
  });
  //permet de garder les grilles a jour quand on recharge la page
  Battleship.registerAction({
    previousGameStates: '*',
    currentGameStates: [Battleship.gameState.playing, Battleship.gameState.finished],
    proceed: function (game) {
      $('#placing-instructions, #btnReady').remove();
      $('#cvsPlayer').addClass('disableCanvas');
      thisIsMyTurn = game.play;
      if (thisIsMyTurn) {
        enemyCanvas.classList.add('can-hit');
      } else {
        enemyCanvas.classList.remove('can-hit');
      }
      if(Battleship.gameState.finished != game.state){
        if (thisIsMyTurn) {
          $( "#alert-msg" ).html( "<div class=\"alert-box warning\">This is your turn.</div>" );
        } else {
          $( "#alert-msg" ).html( "<div class=\"alert-box notice\">Your opponent is playing.</div>" );
        }
      }
      sunkBoats = game.sunk_ships.map(function (ship) {
        return {
          size: +ship.size,
          orientation: ship.orientation.toLowerCase(),
          x: gridToCanvas(+ship.x),
          y: gridToCanvas(+ship.y),
          width: (squareSize - 1) * ship.size + (ship.size - 1),
          height: squareSize - 1
        }
      });
      playerHits = game.player_hits;
      enemyHits = game.opponent_hits;
      drawGrid(ctxPlayer, cw, ch, p);
      drawGrid(ctxEnemy, gridWidth, gridHeight, p);
      drawSunkBoats();
      drawBoats();
      drawHits();
    }
  });

  Battleship.run();
  //permet de faire la correspondance entre les bateaux et les "ships" envoye par le serveur
  function getMatchingBoat(ship) {
    return boats.filter(function (boat) {
      return boat.size == ship.size && !boat.id;
    })[0];
  }
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
    // clear le canvas
    ctx.clearRect(0, 0, width + p * 2, height + p * 2);
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
    if (pointedSquare && thisIsMyTurn) {
      highlightSquare(pointedSquare);
    }
  }
  /* dessine tous les bateaux */
  function drawBoats() {
    for (var i=0; i<boats.length; i++){
      var b = boats[i];
      drawBoat(b, ctxPlayer);
    }
  }
  /* dessine tous les bateaux coules */
  function drawSunkBoats() {
    sunkBoats.forEach(function (boat) {
      drawBoat(boat, ctxEnemy, true);
    });
  }
  /* dessine un bateau specifique */
  function drawBoat(boat, context, sunk) {
    context.save();
    context.fillStyle = !sunk ? "#7E7E6C" : "#990000";
    if(boat.orientation == "horizontal"){
      context.fillRect(boat.x, boat.y,boat.width,boat.height);
    }
    else if(boat.orientation == "vertical"){
      context.fillRect(boat.x, boat.y,boat.height,boat.width);
    }
    context.restore();
  }
  //dessine tous les hits
  function drawHits() {
    playerHits.forEach(function (hit) {
      drawHit(hit, ctxEnemy);
    });
    enemyHits.forEach(function (hit) {
      drawHit(hit, ctxPlayer);
    });
  }
  //dessine un hit
  function drawHit(hit, context) {
    var radius = 17;
    context.save();
    context.beginPath();
    context.arc(gridToCanvas(+hit.x) + squareSize / 2, gridToCanvas(+hit.y) + squareSize / 2, radius, 0, 2 * Math.PI, false);
    context.fillStyle = hit.success == '1' ? '#FF9900' : '#5C85FF'; //orange, bleu
    context.fill();
    context.lineWidth = 2;
    context.strokeStyle = hit.success == '1' ? '#CC0000' : '#006600'; // rouge, vert
    context.stroke()
    context.restore();
  }
  //met en evidence une case au passage de la souris sur la grille de l'ennemie
  function highlightSquare(squarePos) {
    ctxEnemy.save();
    ctxEnemy.fillStyle = "rgba(255, 208, 0, 0.6)";
    ctxEnemy.fillRect(gridToCanvas(squarePos.x), gridToCanvas(squarePos.y), squareSize - 1, squareSize - 1);
    ctxEnemy.restore();
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
      draggingBoat.gridX = canvasToGrid(draggingBoat.x);
      draggingBoat.gridY = canvasToGrid(draggingBoat.y);
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
  //converti les pixels en numeros de case
  function canvasToGrid(canvasPos) {
    return Math.ceil((canvasPos - p) / squareSize);
  }
  //transforme les bateaux en "ship" pour correspondre au modele serveur
  function addShipDataToBoat(boat){
    boat.shipData = {
      x: boat.gridX,
      y: boat.gridY,
      size: boat.size,
      orientation: boat.orientation.toUpperCase()
    };
  }
  //fonction qui transforme le bateau horizontal en vertical
  function changeOrientation(boat){
      boat.orientation = boat.orientation == "vertical"?"horizontal":"vertical";
      ctxPlayer.clearRect(0, 0, cw, ch);
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
  //verifie que aucun bateau n'en croise un autre
  function isNotCrossed(boat){
    return boats.every(function (b) {
      if(boat === b){
        return true;
      }
      var boatX = boat.gridX;
      var boatY = boat.gridY;
      for (var boatI = 0; boatI < +boat.size; ++boatI) {
        var bX = b.gridX;
        var bY = b.gridY;
        for (var bI = 0; bI < +b.size; ++bI) {
          if (boatX === bX && boatY === bY) {
            return false;
          }
          if (b.orientation === 'horizontal') {
            ++bX;
          } else {
            ++bY;
          }
        }
        if (boat.orientation === 'horizontal') {
          ++boatX;
        } else {
          ++boatY;
        }
      }
      return true;
    });
  }
  //initialise les deux grilles de jeu
  initEnemyGrid();
  initPlayerGrid();
  //onclick sur le bouton Ready, verifie que tous les bateaux sont bien postionnes dans la grille grace a la fonction boatInGrid
  //et envoi de la requete au serveur
  $('#btnReady').click(function () {
    if (!boats.every(boatInGrid)) {
      $( "#alert-msg" ).html( " <div class=\"alert-box error\"><span>error: </span>All your boats must be <strong>inside</strong> the grid! Please change their positions.</div>" );
    } else if (!boats.every(isNotCrossed)) {
      $( "#alert-msg" ).html( " <div class=\"alert-box error\"><span>error: </span>Your boats should <strong>not cross each others</strong>! Please change their positions.</div>" );
    } else {
      $("#cvsPlayer").addClass("disableCanvas");
      $( "#alert-msg" ).html( "<div class=\"alert-box success\"><span>success: </span>You have place all your boats ! Wait till your opponent is ready now ;).</div>" );
      $("#btnReady").attr('disabled',true);
      boats.forEach(addShipDataToBoat);
      Battleship.api.placeShips(boats, function () {
        console.log('Ship placed');
        Battleship.api.ready(function () {
          console.log('Ready: OK');
        });
      });
    }
  });
  //appel a chaque fois que la souris bouge
  $('#cvsPlayer').mousemove(function (e) {
    if(draggingBoat){
      //change les coord du bateau deplace si il y en a un
      var cvsPlayerOffset = $(e.target).offset();
      var x = e.offsetX === undefined ? e.pageX-cvsPlayerOffset.left : e.offsetX;
      var y = e.offsetY === undefined ? e.pageY-cvsPlayerOffset.top : e.offsetY;
      draggingBoat.x = x;
      draggingBoat.y = y;
      drawGrid(ctxPlayer,cw,ch,p);
      drawBoats();
    }
  });
  //appel au clic gauche (drag & drop du bateau) et clic droit (changement d'orientation)
  $('#cvsPlayer').mousedown(function (e) {
    if(playerReady){
      return;
    }
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

  //appel a chaque fois que la souris bouge
  $('#cvsEnemy').mousemove(function (e) {
    if (gameState === Battleship.gameState.playing && thisIsMyTurn) {
      // met en avant la case survolee
      var cvsPlayerOffset = $(e.target).offset();
      var x = e.offsetX === undefined ? e.pageX-cvsPlayerOffset.left : e.offsetX;
      var y = e.offsetY === undefined ? e.pageY-cvsPlayerOffset.top : e.offsetY;
      var squarePos = {
        x: canvasToGrid(x),
        y: canvasToGrid(y)
      };
      if (squarePos.x < 1 || squarePos.x > 10 || squarePos.y < 1 || squarePos.y > 10) {
        return;
      }
      pointedSquare = squarePos;
      drawGrid(ctxEnemy,gridWidth,gridHeight,p);
      drawBoats();
      drawSunkBoats();
      drawHits();
    }
  });

  //appel au clic gauche : lancement d'un hit
  $('#cvsEnemy').mouseup(function (e) {
    if (!thisIsMyTurn) {
      console.log('This is not my turn!');
      return;
    }
    var cvsOffset = $(e.target).offset();
    var x = e.offsetX === undefined ? e.pageX-cvsOffset.left : e.offsetX;
    var y = e.offsetY === undefined ? e.pageY-cvsOffset.top : e.offsetY;
    if(gameState === Battleship.gameState.playing){ // on ne peut faire des "hits" que quand on est dans l'etat "playing"
      if( e.which == 1 ){
        var hit = {
          x: canvasToGrid(x),
          y: canvasToGrid(y)
        };
        if (hit.x < 1 || hit.x > 10 || hit.y < 1 || hit.y > 10) {
          console.log('Click ignored (out of grid)');
          return;
        }
        if (hitsHistory.some(function (historyHit) { return hit.x === historyHit.x && hit.y === historyHit.y; })) {
          console.log('This hit has already been sent');
          return;
        }
        thisIsMyTurn = false;
        Battleship.api.hit(hit, function (error, result) {
          console.log('Hit:', result);
          if (error) {
            console.error(error);
            thisIsMyTurn = true;
          } else {
            hitsHistory.push(hit);
            pointedSquare = null;
          }
        });
      }
    }
  });
});
