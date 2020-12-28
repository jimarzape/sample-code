var app = require('express')();
var http = require('http').Server(app);
var io = require('socket.io')(http);
var Redis = require('ioredis');
var redis = new Redis();

// registering channels
redis.subscribe(
    [
        'new-order'
    ], 
    function(err, count) {
        console.log(err);
    }
);


redis.on('message', function(channel, message) {
    console.log(channel + ': ' + message);
    try {
        message = JSON.parse(message);
    }
    catch (e) { }
    
    io.emit(channel, message);
});

io.on('connection', (socket) => {

}); 

http.listen(3001, function(){
    console.log('Listening on Port 3001');
});
 
