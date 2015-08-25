//Skrypt aktualnej godziny, by Tarzan
function refreshTime() {
 var refresh = 1000;
 mytime=setTimeout('displayTime()', refresh); }

function displayTime() {
 var time = new Date()
 var hours = time.getHours()
 var minutes = time.getMinutes()
 var seconds = time.getSeconds()
 var day = time.getDate()
 var month = time.getMonth()+(1*1)
 var year = time.getFullYear()

 if (hours < 10) { hours = "0" + hours }
 if (minutes < 10) { minutes = "0" + minutes }
 if (seconds < 10) { seconds = "0" + seconds }
 if (month < 10) { month = "0" + month }
 if (day < 10) { day = "0" + day }

 var time = day+"."+month+"."+year+", "+hours+":"+minutes+":"+seconds;
 document.getElementById('clock').innerHTML = time;

 refreshTime(); }