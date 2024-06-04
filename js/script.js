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

// Clamp a value between min, val, max
function clamp(val, min, max){
    if (val < min){
        return min;
    }
    else if (val > max){
        return max;
    }
    return val;
}

// Add all elements for a comp on the page
async function showCompetition(index){
    comp = clamp(index, 1, 5);

    let temp = [...document.getElementsByClassName("current-page")];
    temp.forEach(e => {
        e.classList.remove("current-page");
    })

    let title = document.querySelector("main h1");
    title.innerHTML = comp < 5 ? "Deltävning " + comp.toString() : "Finalen";

    let navButton = [...document.getElementsByClassName("nav-button")][comp-1];
    navButton.classList.add("current-page");

    if (comp < 5) {
        displaySongs(comp);
    } else {
        displayFinalists();
    }
    
    displayVotes();
    configTime();
}

async function newSongCard(info, final=false){
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
    newCard.appendChild(cardDesc);

    let cardSection = document.querySelector(".gradient section");
    cardSection.appendChild(newCard);

    let currentComp = await getCurrentComp()
    if (comp == currentComp){
        let cardButton = document.createElement("button");
        cardButton.innerHTML = "Rösta"
        cardButton.onclick = function(){vote(info["ID"], final)}
        cardButton.classList.add("rainbow-bg");
        cardDesc.innerHTML = artistInfo["Description"];

        newCard.appendChild(cardButton);
    } else if(comp < currentComp && comp < 5) {
        let finalists = JSON.parse(await fetchData("./admin/requesthandler.php", {"GetTopSongs":true}));
        newCard.style.filter = "grayscale(1)"
        finalists.forEach(song => {
            if (info["ID"] == song["ID"]){
                cardTitle.classList.add("rainbow-text");
                newCard.style.filter = "none";
                return
            }
        });
    } else if (comp < currentComp && comp == 5) {
        let finalists = JSON.parse(await fetchData("./admin/requesthandler.php", {"GetTopSongs":true}));
        let winner = true;
        finalists.forEach(song => {
            if (song["FinalVotes"] > info["FinalVotes"]){
                winner = false;
                return;
            }
        });
        if (winner){
            newCard.classList.add("winner");
            cardSection.insertBefore(newCard, cardSection.firstChild);
            cardDesc.innerHTML = "VINNARE";
            cardDesc.classList.add("rainbow-text");
            cardDesc.style.fontSize = "150%";
        }
    }
    if (comp > currentComp){
        newCard.style.filter = "blur(10px)";
        cardVideo.src = "";
    }
}

function clearSongCards(){
    let cards = document.getElementsByClassName("artist-card");
    [...cards].forEach(e => {
        e.remove();
    });
}


async function displaySongs(compID){
    clearSongCards();
    if (compID > await getCurrentComp()){
        return;
    }
    let response = JSON.parse(await fetchData("./admin/requesthandler.php", {"getCompSongs":compID}));
    response.forEach(e => {
        newSongCard(e);
    });
}

async function getCurrentComp(){
    let response = JSON.parse(await fetchData("./admin/requesthandler.php", {"GetTime":true}));
    let CompDuration = response["CompDuration"];
    let StartTime = parseInt(response["StartTime"]);
    let TimePassed = (new Date).getTime() - StartTime;
    let currentComp = Math.ceil(TimePassed / CompDuration);
    return currentComp;
}

async function configTime(){
    let response = JSON.parse(await fetchData("./admin/requesthandler.php", {"GetTime":true}));
    let CompDuration = response["CompDuration"];
    let StartTime = parseInt(response["StartTime"]);
    let TimePassed = (new Date).getTime() - StartTime;
    let currentComp = Math.ceil(TimePassed / CompDuration);

    // Remove any previous gradient glows in nav
    let temp = [...document.getElementsByClassName("current-comp")];
    temp.forEach(e => {
        e.classList.remove("current-comp");
    })
    // Give the currently active comp, a gradient glow in nav
    let nav = [...document.getElementsByClassName("nav-button")];
    if (nav[currentComp-1]){
        nav[currentComp-1].classList.add("current-comp")
    }

    // If comp has passed
    let timeTitle = document.querySelector("main h2");
    if (comp < currentComp){
        timeTitle.innerHTML = "Voting ended";
    }
    // If comp is in future
    else if(comp > currentComp){
        let sD = new Date(StartTime + (CompDuration*comp));
        let temp = sD.getFullYear()+"/"+sD.getMonth()+"/"+sD.getDate();
        temp += " "+String(sD.getHours()-1).padStart(2, "0")+":"+String(sD.getMinutes()).padStart(2, "0");
        timeTitle.innerHTML = "Begins "+ temp;
    }
    // If comp is ongoing
    else if(comp == currentComp){
        let sD = new Date(StartTime + (CompDuration*(comp+1)));
        let temp = sD.getFullYear()+"/"+sD.getMonth()+"/"+sD.getDate();
        temp += " "+String(sD.getHours()-1).padStart(2, "0")+":"+String(sD.getMinutes()).padStart(2, "0");
        timeTitle.innerHTML = "Voting until "+temp;
    } 
}

// MAKE IT SO THAT THE USERS VOTE RESET IF STARTTIME IS CHANGED
// I REPEAT
// DO WHAT I SAID

async function displayVotes(){
    let votesLeft = document.getElementById("votes-left");

    if (comp != await getCurrentComp()){
        votesLeft.innerHTML = "";
        return;
    }

    let Votes = !isNaN(localStorage.getItem("Votes")) ? parseInt(localStorage.getItem("Votes") ) : 0;
    let refillVotes = false

    if (Votes == NaN){
        Votes = 0;
    }

    // If current comp is larger than lastComp, refill votes
    let lastComp = localStorage.getItem("lastComp")

    // If lastComp isnt a number, save current comp as last comp
    if (isNaN(lastComp)){
        localStorage.setItem("lastComp", comp);
        refillVotes = true;
    // If lastComp is a number
    } else if (!isNaN(lastComp)){
        if (parseInt(lastComp) < comp){
            refillVotes = true;
            localStorage.setItem("lastComp", comp);
        }
        else if (parseInt(lastComp) > comp){
            localStorage.setItem("lastComp", comp);
        }
    }

    Votes = refillVotes ? 0 : Votes;
    localStorage.setItem("Votes", Votes);
    votesLeft.innerHTML = "Röster kvar: "+(3-Votes)
}

// Vote for a song
async function vote(songID, final=false){
    // Require cookies
    if (!localStorage.getItem("Cookies")){
        alert("Accept cookies to vote (refresh page)");
        return;
    }

    displayVotes();
    // Get how many votes user has
    let Votes = !isNaN(localStorage.getItem("Votes")) ? parseInt(localStorage.getItem("Votes") ) : MAXVOTES;
    
    // Return if Votes is max
    if (Votes >= MAXVOTES){
        alert("ALL VOTES USED");
        return;
    }
    await fetchData("./admin/requesthandler.php", {"Vote":songID, "Final":final});
    console.warn("VOTED");

    // Update user votes
    localStorage.setItem("Votes", Votes+1);
    displayVotes();
}

// Create cards for artists on "Final" page
async function displayFinalists(){
    clearSongCards();
    if (comp > await getCurrentComp()){
        return;
    }
    // Fetch top scoring songs
    let finalists = JSON.parse(await fetchData("./admin/requesthandler.php", {"GetTopSongs":true}));
    
    finalists.forEach(song => {
        newSongCard(song, true);
    });
    
    
    return finalists;
}

function acceptCookies(){
    localStorage.setItem("Cookies", "true");
    hideCookiesDisplay();
}

function hideCookiesDisplay(){
    cookiesWindow.style.display = "none";
}

async function loadComp(){
    comp = clamp(await getCurrentComp(), 1, 5)
    showCompetition(comp);
}


var cookiesWindow = document.getElementById("cookies-window");
if (localStorage.getItem("Cookies")){
    cookiesWindow.style.display = "none";
}

var comp = 1;
loadComp();
const MAXVOTES = 3;

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