'use strict';

var recorder = null, graph = null;

function initRecorder() {
    recorder = new LinguaRecorder();
    recorder.on( 'started', function() {
        document.querySelector('.rcd-cancel').style.visibility = "visible";
        document.querySelector('.rcd-timer').style.visibility = "visible";
        document.querySelector('.bdls-input').style.visibility = "hidden";
        fbtn.setAttribute('data', "text");
    }).on( 'recording', function(audioRecord) {
        addTime(audioRecord);
    }).on( 'stoped', function(audioRecord) {
        document.querySelector('.fbtn > div').classList.add('dark:bg-red-800/20');
        document.querySelector('.fbtn > div > svg').style.stroke = "#916060";
        document.querySelector('.rcd-cancel').style.visibility = "hidden";
        document.querySelector('.rcd-timer').style.visibility = "hidden";
        document.querySelector('.fbtn').disabled = "true";
        addSound( audioRecord );
    }).on( 'canceled', function() {
        document.querySelector('.rcd-cancel').style.visibility = "hidden";
        document.querySelector('.rcd-timer').style.visibility = "hidden";
        document.querySelector('.bdls-input').style.visibility = "visible";
        fbtn.setAttribute('data', "play");
    });
}

function addTime(audioRecord){
    var time = (Math.round(audioRecord.getDuration() * 1000 ) / 1000).toFixed(0);
    document.querySelector('.rcd-timer label').innerHTML = '0:'+time.toString().padStart(2, '0');
}


function addSound( audioRecord ) {
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var audio64;
    var reader = new window.FileReader();
    reader.readAsDataURL(audioRecord.getBlob());
    reader.onloadend = function(){
        audio64 = reader.result;

        const rform = document.createElement('form');
        rform.method = "POST";
        var params = {_token: token, audio: audio64, tz: document.getElementById('tzo').value};
        for(const key in params){
            const field = document.createElement('input');
            field.type = 'hidden';
            field.name = key;
            field.value = params[key];
    
            rform.appendChild(field);
        }
        // // multipart/form-data
        document.body.appendChild(rform);
        rform.submit();
        showLastMsg('<i>Transcribing...</i>', true, audio64);
    }
}

function gotoRecord(){
    if(fbtn.getAttribute("data") == "play") recorder.start();
    else recorder.stop();
}

function rcdCancel(){
    recorder.cancel();
}

document.addEventListener('play', function(e){  
    var players = document.getElementsByTagName('audio');  
    for(var i = 0; i < players.length; i++){  
        if(players[i] != e.target && !players[i].paused && players[i].currentTime > 0 && !players[i].ended){  
            players[i].pause();  
        }  
    }
}, true);