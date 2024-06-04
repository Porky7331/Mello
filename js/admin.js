async function fetchData(fetchUrl, objectToSend) {
    try {
        // Create formdata with objectToSend
        let formData = new FormData();
        for (let key in objectToSend) {
            formData.append(key, objectToSend[key]);
        }
        
        // Wait for response
        let response = await fetch(fetchUrl, {
            method: "POST",
            body: formData
        });

        // If problem with response
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }

        // Retrieve respone and return
        var resultFromPHP = await response.text();
        return resultFromPHP;
        
    } catch (error) {
        console.error('There was a problem with the fetch operation:', error);
    }
}

// Delete song
async function deleteSong(){
    if (confirm("Are you sure?")) {
        let sendData = {};
        sendData.SongID = currentSongID;
        sendData.ArtistID = currentArtistID;
        sendData.deleteSong = true;
        let response = await fetchData("../admin/requesthandler.php", sendData);
        songList();
        editSongSection.classList.add("hidden");
    }
}

// Edit song
async function editSong(){
    let sendData = {};
    sendData.SongID = currentSongID;
    sendData.ArtistID = currentArtistID;
    sendData.ArtistName = document.querySelector("#ArtistName").value;
    sendData.ArtistDescription =  document.querySelector("#ArtistDescription").value;
    sendData.SongName = document.querySelector("#SongName").value;
    sendData.VideoURL = document.querySelector("#VideoURL").value;
    sendData.Votes = document.querySelector("#Votes").value;
    sendData.editSong = true;
    await fetchData("../admin/requesthandler.php", sendData);
    songList();
}

// Bring up section to edit songs
async function showEditSong(songID, SongName, VideoURL, Votes, ArtistID){
    editSongSection.classList.remove("hidden");
    let response = await fetchData("../admin/requesthandler.php", {"getCompSongs":comp});
    let songs = JSON.parse(response);

    response = await fetchData("../admin/requesthandler.php", {"getArtistFromID":ArtistID});
    let artist = JSON.parse(response);
    console.log(artist);

    document.querySelector("#ArtistName").value = artist["Name"];
    document.querySelector("#ArtistDescription").value = artist["Description"];

    document.querySelector("#SongName").value = SongName;
    document.querySelector("#VideoURL").value = VideoURL;
    document.querySelector("#Votes").value = Votes;

    currentSongID = songID;
    currentArtistID = ArtistID;
}

// Add new song
async function addSong(){
    let response = await fetchData("../admin/requesthandler.php", {"addSong":comp});
    if (JSON.parse(response) == "limitReached"){;
        alert("Song Limit reached");
    } 
    songList();
}

// Display all songs in currently selected comp
async function songList(){
    let div = document.getElementsByClassName("songListDiv")[0];
    div.innerHTML = "";
    pickCompDiv.innerHTML = "";
    
    let response;
    if (comp <= 4){
        response = await fetchData("../admin/requesthandler.php", {"getCompSongs":comp});
    } else{
        response = await fetchData("../admin/requesthandler.php", {"GetTopSongs":comp});
    }
    
    let songs = JSON.parse(response);
    songs.forEach(song => {
        let newButton = document.createElement('button');
        newButton.innerHTML = song["SongName"];
        newButton.onclick = function () { showEditSong(parseInt(song["ID"]), song["SongName"], song["VideoURL"], song["Votes"], song["ArtistID"]); };
        div.appendChild(newButton);
    });

    for (let i=1; i<6; i++){
        let newButton = document.createElement('button');
        newButton.innerHTML = i;
        newButton.onclick = function () { pickComp(i); };
        pickCompDiv.appendChild(newButton);
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

// Pick what competition to edit
function pickComp(val){
    comp = clamp(val, 1, 5);
    editSongSection.classList.add("hidden");
    songList();
    loadTime();
}

// Load time setting saved
async function loadTime(){
    try {
        let response = JSON.parse(await fetchData("../admin/requesthandler.php", {"GetTime":true}));
        let StartTime = document.querySelector("#StartTime");
        let CompDuration = document.querySelector("#CompDuration");

        let date = new Date(parseInt(response["StartTime"]));
        let dateString = "";
        // yyyy-
        dateString += date.getFullYear()+"-";
        // mm-
        dateString += String(date.getMonth()+1).padStart(2, '0')+"-";
        // ddT
        dateString += String(date.getDate()).padStart(2, '0')+"T";
        // hh:
        dateString += String(date.getHours()).padStart(2, '0')+":";
        // dd
        dateString += String(date.getMinutes()).padStart(2, '0');

        CompDuration.value = parseInt(response["CompDuration"]) / 3600000;
        StartTime.value = dateString;

    } catch (error) {
        console.log("Need to select date! Error:", error);
        return;
    }  
}

// Set start time and duration for each competition
async function setTime(){
    try {
        let StartTime = document.querySelector("#StartTime").value;
        toString();
        let date = StartTime.split("T");
        
        let y = date[0].split("-")[0];
        let m = date[0].split("-")[1]-1;
        let d = date[0].split("-")[2];
    
        let h = date[1].split(":")[0];
        let min = date[1].split(":")[1];
    
        let newDate = new Date(y, m, d, h, min, 0, 0);
        let epochTime = newDate.getTime();
        console.log(newDate, y, m, d, h, min);
    
    
        let CompDuration = document.querySelector("#CompDuration").value;
        CompDurationSeconds = CompDuration * 3600000;
        console.log(epochTime, (new Date).getUTCSeconds());

        let sendData = {};
        sendData.StartTime = epochTime;
        sendData.CompDuration = CompDurationSeconds;
        sendData.SetTime = true;
        let response = await fetchData("../admin/requesthandler.php", sendData);
        console.log(response);
    } catch (error) {
        console.log("Need to select date! Error:", error);
        return;
    }   
}

var comp = 1;
var currentSongID = 0;
var currentArtistID = 0;
let editSongSection = document.getElementById("editSongSection");
let pickCompDiv = document.getElementById("pickCompDiv");
songList();
loadTime();