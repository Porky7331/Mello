function showCompetition(part = 1){
    console.log("hello", part);
}

async function fetchData(fetchUrl, objectToSend) {
    try {
        let formData = new FormData();
        for (let key in objectToSend) {
            formData.append(key, objectToSend[key]);
        }
        
        let response = await fetch(fetchUrl, {
            method: "POST",
            body: formData
        });

        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        var resultFromPHP = await response.text();
        return resultFromPHP;
        
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error);
    }
}

function editSong(songID){
    console.log("tesst", songID)
}

async function selectComp(){
    console.log("add song");
    let response = await fetchData("../admin/requesthandler.php", {"addSong":comp});
    console.log(response);
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

