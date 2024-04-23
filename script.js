
function showCompetition(part = 1){
    console.log("hello", part);
}

var mobile = false;
if (document.body.clientHeight > document.body.clientWidth){
    mobile = true;
}

if (mobile) {
    var artistCards = [...document.getElementsByClassName("artist-card")];
    artistCards.forEach(element => {
    element.style.width = "90%";
}); 
}        