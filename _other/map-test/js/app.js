// A cross-browser requestAnimationFrame
// See https://hacks.mozilla.org/2011/08/animating-with-javascript-from-setinterval-to-requestanimationframe/
var requestAnimFrame = (function () {
    return window.requestAnimationFrame ||
        window.webkitRequestAnimationFrame ||
        window.mozRequestAnimationFrame ||
        window.oRequestAnimationFrame ||
        window.msRequestAnimationFrame ||
        function (callback) {
            window.setTimeout(callback, 1000 / 60);
        };
})();

// Create the canvas
var lastTime  = Date.now();
var canvas    = document.createElement("canvas");
var ctx       = canvas.getContext("2d");
canvas.width  = 976;
canvas.height = 480;
document.body.appendChild(canvas);

resources.load([
    'img/trainer.gif',
    'img/sprites.png',
    'img/map.png'
]);

resources.onReady(function() {
    ctx.drawImage(resources.get('img/map.png'), 0, 0);
    main();
});

var player = {
    pos: [30, 30],
    sprite: new Sprite('img/trainer.gif', [0, 0], [16, 21], 4, [0, 1, 2, 1, 0], 'horizontal', true)
};


// The main game loop
function main() {
    var now = Date.now();
    var dt = (now - lastTime) / 1000.0;
    handleInput();
    player.sprite.update(dt);
    renderEntity(player);
    lastTime = now;
    requestAnimFrame(main);
}

function renderEntity(entity) {
    ctx.save();
    ctx.translate(entity.pos[0], entity.pos[1]);
    entity.sprite.render(ctx);
    ctx.restore();
}

function handleInput() {
    
    if(!player.sprite.done) return;
    
    if (input.isDown('DOWN')) {
        player.endPoint = player.pos[1] + 16;
        player.pos[1] += 4;
        player.sprite.pos = [0, 0];
        player.sprite.done = false;
    } else if (input.isDown('UP')) {
        player.pos[1] -= 4;
        player.sprite.pos = [0, 21];
        player.sprite.done = false;
    } else if (input.isDown('LEFT')) {
        player.pos[0] -= 4;
        player.sprite.pos = [0, 63];
        player.sprite.done = false;
    } else if (input.isDown('RIGHT')) {
        player.pos[0] += 4;
        player.sprite.pos = [0, 42];
        player.sprite.done = false;
    }
    
}

