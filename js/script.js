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

function clamp(val, min, max){
    if (val < min){
        return min;
    }
    else if (val > max){
        return max;
    }
    return val;
}

function editSong(songID){
    console.log("tesst", songID)
}

async function showCompetition(index){
    comp = clamp(index, 1, 5);
    displaySongs(comp);
    getTime();
}

async function newSongCard(info){
    let artistInfo = JSON.parse(await fetchData("./admin/requesthandler.php", {"getArtistFromID":info["ArtistID"]}));

    let newCard = document.createElement("div");
    newCard.classList.add("artist-card");

    let cardTitle = document.createElement("h3");
    cardTitle.innerHTML = info["SongName"];
    newCard.appendChild(cardTitle);

    let cardArtist = document.createElement("h4");
    cardArtist.innerHTML = artistInfo["Name"];
    newCard.appendChild(cardArtist);

    let cardVideo = document.createElement("iframe");
    cardVideo.src = "https://youtube.com/embed/" + info["VideoURL"].split("=")[1].split("&")[0];
    cardVideo.allowFullscreen = true;
    newCard.appendChild(cardVideo);

    let cardDesc = document.createElement("p");
    cardDesc.innerHTML = artistInfo["Description"];
    newCard.appendChild(cardDesc);

    let cardButton = document.createElement("button");
    cardButton.innerHTML = "Rösta"
    cardButton.onclick = "vote()";
    cardButton.classList.add("rainbow-bg");
    newCard.appendChild(cardButton);


    let cardSection = document.querySelector(".gradient section");
    cardSection.appendChild(newCard);
}


async function displaySongs(compID){
    let cards = document.getElementsByClassName("artist-card");
    [...cards].forEach(e => {
        e.remove();
    });
    let response = JSON.parse(await fetchData("./admin/requesthandler.php", {"getCompSongs":compID}));
    response.forEach(e => {
        newSongCard(e);
    });
}

async function getTime(){
    console.log("add song");
    let response = JSON.parse(await fetchData("./admin/requesthandler.php", {"GetTime":true}));
    let CompDuration = response["CompDuration"];
    let StartTime = parseInt(response["StartTime"]);
    let TimePassed = (new Date).getTime() - StartTime;

    let currentComp = Math.floor(TimePassed / CompDuration);
    console.log(TimePassed, CompDuration, timeIndex)
}

var comp = 1
displaySongs(comp)

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

//var body = document.body,
//    html = document.documentElement;

//var height = document.documentElement.scrollHeight;
//document.getElementById("bg-glow").style.height = height.toString()+"px";
//console.log(height.toString()+"px");