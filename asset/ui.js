function _(elt){
    return document.getElementById(elt);
}

_("sendsms")?.addEventListener("click", send_sms);

// Mémorise le dernier contenu reçu par élément (mode remplacement) pour
// éviter de réécrire le DOM quand rien n'a changé — sinon la liste clignote
// à chaque reconnexion SSE (toutes les quelques secondes).
var _lastData = {};

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
                // Liste utilisateurs : on ne reconstruit rien si le contenu global
                // est identique ; sinon on met à jour ligne par ligne.
                if(_lastData[id] === e.data) return;
                _lastData[id] = e.data;
                reconcileList(_(id), e.data);
            }
        });
    } else {
        _(id).innerHTML = "Sorry, your browser does not support server-sent events...";
    }
}

// Met à jour une liste sans tout reconstruire : chaque élément porte une clé
// stable (data-id). On compare l'existant à la nouvelle version et on ne touche
// que ce qui a changé — ligne modifiée (ex : statut), ajout, ou suppression.
function reconcileList(container, html){
    var parsed = new DOMParser().parseFromString(html, "text/html");
    var incoming = {};
    var order = [];
    parsed.querySelectorAll("[data-id]").forEach(function(node){
        var key = node.getAttribute("data-id");
        incoming[key] = node;
        order.push(key);
    });

    // 1. Parcourt l'existant : met à jour si différent, retire si disparu
    container.querySelectorAll("[data-id]").forEach(function(existing){
        var key = existing.getAttribute("data-id");
        var fresh = incoming[key];
        if(!fresh){
            existing.remove();                        // utilisateur parti de la liste
        } else {
            if(existing.outerHTML !== fresh.outerHTML){
                existing.replaceWith(fresh);          // ex : statut on/off modifié
            }
            delete incoming[key];                     // déjà traité
        }
    });

    // 2. Ajoute les lignes restantes (nouveaux inscrits), dans l'ordre du serveur
    order.forEach(function(key){
        if(incoming[key]) container.appendChild(incoming[key]);
    });
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