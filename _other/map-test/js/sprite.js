(function () {
    function Sprite(url, pos, size, speed, frames, dir, once) {
        this.pos    = pos;
        this.size   = size;
        this.speed  = typeof speed === 'number' ? speed : 0;
        this.frames = frames;
        this._index = 0;
        this.url    = url;
        this.dir    = dir || 'horizontal';
        this.once   = once;
    }

    Sprite.prototype = {
        update: function (dt) {
            console.log(this._index);
            if(this.done) return;
            this._index += this.speed * dt;
        },

        render: function (ctx) {
            if(this.done) return;
            var max = this.frames.length;
            var idx = Math.floor(this._index);
            var frame = this.frames[idx % max];
            if (this.once && idx >= max) {
                console.log(idx);
                this.done = true;
                this._index = 0;
                return;
            }


            var x = this.pos[0];
            var y = this.pos[1];

            x += frame * this.size[0];

            ctx.drawImage(resources.get(this.url), x, y, this.size[0], this.size[1], 0, 0, this.size[0], this.size[1]);
        }
    };

    window.Sprite = Sprite;
})();