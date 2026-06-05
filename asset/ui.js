function _(elt){
    return document.getElementById(elt);
}

function event(src, eventName, id, append){
    if(typeof(EventSource) !== "undefined") {
        var source = new EventSource(src, { withCredentials: true });
        source.addEventListener(eventName, (e) => {
            if(e.data.trim() === "") return;
            if(append){
                // Messages : ajoute uniquement les nouveaux à la suite
                _(id).insertAdjacentHTML('beforeend', e.data);
                _(id).scrollTop = _(id).scrollHeight;
            } else {
                // Liste utilisateurs : remplace (la liste entière change)
                _(id).innerHTML = e.data;
            }
        });
    } else {
        _(id).innerHTML = "Sorry, your browser does not support server-sent events...";
    }
}

function send_sms(){
    var xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function(){
        if(xhr.readyState==4 && xhr.status==200){
            _("content").value='';
        }
    };
    xhr.open("POST","server.php",true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send(
        "task=envoyer"+
        "&content=" + encodeURIComponent(_("content").value)+
        "&csrf=" + encodeURIComponent(_("csrf").value)
    );
}