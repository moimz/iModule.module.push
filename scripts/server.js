#!/usr/local/bin/node

var Server = function(opt) {
	this.socket = require('socket.io')(3000);
	
	this.socket.on("connection",function(socket) {
		console.log("someone connected");
		
		socket.on("disconnect",function(socket) {
			console.log("someone disconnect");
		});
		
		socket.on("join",function(channels) {
			console.log("join",channels);
			while (channels.length > 0) {
				socket.join(channels.pop());
			}
		});
		
		socket.on("push",function(data) {
			var channel = data[0];
			var data = data[1];
			socket.to(channel).emit("push",data);
			socket.emit("push",data);
			console.log(channel,data);
		});
		//client.socket.io.disconnect()
	});
	
	
};

var server = new Server({
	
});