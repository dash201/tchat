function _(elt){
    return document.getElementById(elt);
}

function event(src, event, id){
    if(typeof(EventSource) !== "undefined") {
        var source = new EventSource(src,  { withCredentials: true });
        source.addEventListener(event,(e)=>{
            _(id).innerHTML = e.data+"<br/>";
        });
    } else {
        _(id).innerHTML = "Sorry, your browser does not support server-sent events...";
    }
}

function send_sms(){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState==200 && xhr.status==4){
            _("content").value='';
        }
    };
    xhr.open("GET","server.php?task=envoyer&content="+_("content").value,true);
    xhr.send(null);
}