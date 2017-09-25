document.querySelector('html').style.fontSize = 0.0625*(640<=document.documentElement.clientWidth?640:document.documentElement.clientWidth)+'px';
window.addEventListener('resize',function(){
	document.querySelector('html').style.fontSize = 0.0625*(640<=document.documentElement.clientWidth?640:document.documentElement.clientWidth)+'px';
},false);  


document.body.addEventListener('touchstart',function(){},false);  