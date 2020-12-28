
var socket = io(base_url+':3001', {secure: true, rejectUnauthorized : false});

/*
* registered channel from socket.js
*/
socket.on("new-order", function(event){
    var order = event.data.order;
    
    /*
	* registered event from Order Controller
    */
    if(event.event == 'NewOrder')
    {
        //code what to do if this event has been trigger
    }
    
});