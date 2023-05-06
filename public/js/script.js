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
                showLastMsg(document.querySelector('.bdls-input').value, false, 0);
                document.querySelector('.bdls-input').disabled = "true";
                document.querySelector('.bdls-input').value = "";
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
        }else{
            if((e.which == 10 || e.which === 13) && e.ctrlKey){
                if(!e.repeat){
                    const nAction = new Event('submit', {cancelable: true});
                    e.target.form.dispatchEvent(nAction);
                }
                e.preventDefault();
            }
        }
    }

    document.querySelector('.bdls-input').addEventListener('keydown', enterSubmit);
    document.querySelector('.bdls-input').addEventListener('input', function() {
        
    });

    function getTZ() {
        function z(n){return (n<10? '0' : '') + n}
        var offset = new Date().getTimezoneOffset();
        var sign = offset < 0? '+' : '-';
        offset = Math.abs(offset);
        return sign+','+z(offset/60 | 0)+','+z(offset%60);
    }

    document.getElementById('tzo').value = getTZ();

    function showLastMsg(input,aud,val){
        const zd = new Date();
        zh = zd.getHours(); zm = zd.getMinutes();
        var zh = (zh>12)?zh-12 : zh;
        var zti = (zh<12)?"AM" : "PM";
        zh = (zh == 0) ? 12 : zh;
        var times = padUp(zd.getDate(),2)+"/"+padUp(zd.getMonth()+1,2)+"/"+zd.getFullYear().toString().slice(2)+'&emsp;'+padUp(zh,2)+':'+padUp(zm,2)+' '+zti;
        var val = (aud)?`
          <audio controls controlsList="nodownload"><source src="${val}" type="audio/ogg"></audio>`:'';
        var new_box_c = `<div class="boxc">
        <div class="flex" style="flex-direction: row-reverse; margin: 0 0 0 auto;">
            <div>
                <div class="relative h-16 w-16 bg-red-50 dark:bg-red-800/20 flex items-center justify-center rounded-full">
                    <img class="h-166 w-auto" src="${src}" alt="Logo" />
                    <span class="w-166">${user}</span>
                </div>
            </div>
            <a href="javascript:void(0);" class="scale-100 p-3 bg-white dark:bg-gray-800/50 dark:bg-gradient-to-bl from-gray-700/50 via-transparent dark:ring-1 dark:ring-inset dark:ring-white/5 rounded-lg shadow-2xl shadow-gray-500/20 dark:shadow-none flex motion-safe:hover:scale-[1.01] transition-all duration-250 focus:outline focus:outline-2 focus:outline-red-500" style="flex-direction: column; margin-bottom: 10px;">
                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed c-content">${input}</p>
                ${val}
                <small style="position: absolute; bottom: -18px; right: 0; white-space: nowrap;font-size: x-small;" class="text-gray-900 dark:text-bisque">${times}</small>
            </a>                           
        </div>
    </div>`;
        document.querySelector('.box-c-p').innerHTML += new_box_c;
        document.querySelector('.bdls-input').value = "";
    }
    function padUp(num, len) {
        return num.toString().padStart(len, '0');
    }