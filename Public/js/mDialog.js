;(function(w,d){
    w.addEventListener('load',function(){d.body.addEventListener('touchstart',function(){},false); },false);
    function g(s){return d.querySelector(s)};
    var css = ".mobile-dialog{animation:toFadeIn .4s ease-out forwards;-webkit-animation:toFadeIn .4s ease-out forwards;width:100%;height:100%;position:fixed;z-index:9999;left:0;top:0;background:rgba(0,0,0,.4);color:#222;font-size:14px}.mobile-dialog *{box-sizing:border-box;-webkit-tap-highlight-color:transparent}.mobile-dialog-wrap{max-width:400px;width:80%;position:absolute;left:50%;top:47%;background:#fff;height:auto;border-radius:3px;overflow:hidden;box-shadow:0 0 8px rgba(0,0,0,.1);transform:translate3d(-50%,-50%,0);-webkit-transform:translate3d(-50%,-50%,0)}.mobile-dialog-title{padding:12px 10px;border-bottom:1px solid #e7e7e7;background:#f7f7f7;position:relative}.mobile-close{display:block;position:absolute;right:0;top:0;height:100%;width:40px}.mobile-dialog-btn{border-top:1px solid #e7e7e7;overflow:hidden;background:#f7f7f7}.mobile-dialog-btn button{display:block;float:left;padding:10px 0;margin:0;width:100%;background:0 0;border:none;border-radius:0;font-size:14px;cursor:pointer;border-left:1px solid #e7e7e7}.mobile-dialog-btn button:first-child{border-left:none}.mobile-close:after{content:'';display:block;width:48%;height:1px;background:#222;transform:rotate(45deg);-webkit-transform:rotate(45deg);position:absolute;left:26%;top:50%}.mobile-close:before{content:'';display:block;width:48%;height:1px;background:#222;transform:rotate(-45deg);-webkit-transform:rotate(-45deg);position:absolute;left:26%;top:50%}.mobile-close:active,.mobile-dialog-btn button:active{background:#e8e8e8}.mobile-dialog-con{word-break:break-all;word-wrap:break-word;padding:14px 10px;line-height:20px}.mobile-dialog-btn button{width:50%}.mobile-dialog-box{position:absolute;left:0;top:0;right:0;bottom:0;animation:scaleAlt .5s ease-out forwards}@keyframes scaleAlt{0%{transform:scale(1.2);opacity:0}100%{transform:scale(1);opacity:1}}@-webkit-keyframes scaleAlt{0%{-webkit-transform:scale(1.2);opacity:0}100%{-webkit-transform:scale(1);opacity:1}}@keyframes toFadeIn{from{opacity:0;width:100%}to{opacity:1}}@-webkit-keyframes toFadeIn{from{opacity:0}to{opacity:1}}",
        style = d.createElement('style'),f;
        style.type = 'text/css';
        style.appendChild(d.createTextNode(css));
        g('head').appendChild(style);
    function _(a,b){
        this.clickClose =b && b.clickClose===false ? false : true;
        this.init(a,b);
        this.bind();
    };
    _.prototype = {
        init : function(a,b){
            var t = this;close();
            b && b.after ? (f = b.after) : '';
            d.body.insertAdjacentHTML("beforeend", '<div class="mobile-dialog" ontouchmove="return false"><div class="mobile-dialog-box"><div class="mobile-dialog-wrap"><div class="mobile-dialog-con" style="'+ ((b && b.style) ? b.style : '') +'"></div></div></div></div>');
            var con = g('.mobile-dialog-con');
            if(b && b.title){
                con.insertAdjacentHTML("beforebegin", '<div class="mobile-dialog-title">'+ b.title +'<a href="javascript:;" class="mobile-close"></a></div>');
                g('.mobile-close').addEventListener('click',function(){
                    close();
                },false);
            };
            if(b && b.className){g('.mobile-dialog-wrap').classList.add(b.className);};
            if(b && b.button){
                con.insertAdjacentHTML("afterend", '<div class="mobile-dialog-btn"></div>');
                var _btn = g('.mobile-dialog-btn');
                b.button.forEach(function(i,n){
                    _btn.insertAdjacentHTML("beforeend", '<button style="width :'+ 100/b.button.length+'%">'+i.name+'</button>');
                    _btn.querySelectorAll('button')[n].onclick =function(ev){
                        close();
                        i.callBack && i.callBack.call(t,ev);
                    }; 
                });
            };
            (b && b.innerText) ? ( con.textContent = '<div class="dialog-inner">'+a+'</div>'  ) : (con.innerHTML = '<div  class="dialog-inner">'+a+'</div>');
            b && b.before && b.before.call(con);
        },
        bind : function(){
            // g('.mobile-dialog-wrap').onclick = function(e){e.stopPropagation();};
            // this.clickClose && g('.mobile-dialog').addEventListener('click',function(e){close();},false);
        }
    };
    function close(){try{d.body.removeChild(g('.mobile-dialog'));f && f();f=null}catch(e){};}
    w.alerts = function(a,b){ new _(a,b);};
    w.alerts.close = close;
})(window,document);