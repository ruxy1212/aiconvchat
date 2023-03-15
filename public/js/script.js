    var fbtn = document.querySelector('.fbtn');

    function scrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
        initRecorder();
        document.querySelector('.bdls-input').setAttribute('autofocus','');
    }
    history.scrollRestoration = "manual";
    window.onload = scrollToBottom; 

    function txAdjust(el){
        el.style.height="1px";
        el.style.height=(el.scrollHeight<200)?(el.scrollHeight)+"px":(200)+"px";
        (el.value == "") ? fbtn.setAttribute('data', "play") : fbtn.setAttribute('data', "text");
    }

    function freshConv(){
        var res = confirm("Are you sure you want to restart? All conversations will be erased.")
        if(res == true){    
            initRecorder(); 
            //throw Blob away if it is an audio      
            window.location = "reset/";            
        } else return;
    }

    function openExt(){
        document.querySelector('.bdrop').classList.toggle('bdrop-real');
        document.querySelector('.boxr').classList.toggle('boxr-real'); 
        document.querySelectorAll('body > div')[0].classList.toggle('unsee');
    }

    document.addEventListener('mouseup', function (e) { 
        var con = document.querySelectorAll('body > div')[0], extcon = con.querySelector('div > div > div > div');
        var extbtn = document.querySelectorAll('header a')[1];
        if(!extcon.contains(e.target) && !extbtn.contains(e.target)) {
            con.classList.add('unsee');
            document.querySelector('.boxr').classList.remove('boxr-real'); 
        }
    });

    document.addEventListener('scroll', function(){ 
        if(document.querySelector('.max-w-7xl').getBoundingClientRect().top < -30){
            document.querySelectorAll('img')[1].style.visibility = "hidden";
            document.querySelectorAll('img')[0].style.visibility = "visible";
        }else{
            document.querySelectorAll('img')[0].style.visibility = "hidden";
            document.querySelectorAll('img')[1].style.visibility = "visible";
        }
    //     var cbox = document.querySelector('.max-w-7xl').getBoundingClientRect();
    //     //, rbox = document.querySelector('.boxr').getBoundingClientRect();
    //     if(cbox.top < -10) document.querySelector('.boxr').style.position = "sticky";
    //     else document.querySelector('.boxr').style.position = "fixed";
    });

    if((document.querySelector('.max-w-7xl').getBoundingClientRect().height+100)>document.body.getBoundingClientRect().height) document.querySelector('.boxr').style.position = "sticky"; 

    const form = document.querySelectorAll('form')[1];
    form.addEventListener('submit', function(e){
        e.preventDefault();
        if(fbtn.getAttribute("data") == "text" && document.querySelector('.bdls-input').value != ""){
            if(!document.querySelector('.bdls-input').checkValidity()){
                document.querySelector('.bdls-input').reportValidity();
            }else {
                document.querySelector('.fbtn > div').classList.add('dark:bg-red-800/20');
                document.querySelector('.fbtn > div > svg').style.stroke = "#916060";
                document.querySelector('.fbtn').disabled = "true";
                form.submit();
            }
        }else{
            gotoRecord();
        }        
    });

    var config = document.getElementById('config');
    config.addEventListener('click', switchConfig, false);
    var currConfig = localStorage.getItem('config');
    if (currConfig) {
        if (currConfig === 'enter') {
            config.checked = true;
        }
    }

    function switchConfig(e) {
        if (e.target.checked) {
            localStorage.setItem('config', 'enter');
            currConfig = 'enter';
        } else {        
            localStorage.setItem('config', 'click');
            currConfig = 'click';
        }    
    }

    function enterSubmit(e){
        if(currConfig === 'enter'){
            if(e.which === 13 && !e.shiftKey){
                if(!e.repeat){
                    const nAction = new Event('submit', {cancelable: true});
                    e.target.form.dispatchEvent(nAction);
                }
                e.preventDefault();
            }
        }
    }

    document.querySelector('.bdls-input').addEventListener('keydown', enterSubmit);

    function getTZ() {
        function z(n){return (n<10? '0' : '') + n}
        var offset = new Date().getTimezoneOffset();
        var sign = offset < 0? '+' : '-';
        offset = Math.abs(offset);
        return sign+','+z(offset/60 | 0)+','+z(offset%60);
    }

    document.getElementById('tzo').value = getTZ();