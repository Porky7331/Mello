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
    let response = await fetchData("../admin/requesthandler.php", sendData);
}

async function showEditSong(songID, SongName, VideoURL, Votes, ArtistID){
    let editSongSection = document.getElementById("editSongSection");
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

async function addSong(){
    let response = await fetchData("../admin/requesthandler.php", {"addSong":comp});
    if (JSON.parse(response) == "limitReached"){
        console.log("LIMIIIT");
        alert("Limit reached (max 6 songs)");
    } 
    songList();
}

async function songList(){
    let div = document.getElementsByClassName("songListDiv")[0];
    div.innerHTML = "";
    
    let response = await fetchData("../admin/requesthandler.php", {"getCompSongs":comp});
    let songs = JSON.parse(response);
    songs.forEach(song => {
        let newButton = document.createElement('button');
        newButton.innerHTML = song["SongName"];
        newButton.onclick = function () { showEditSong(parseInt(song["ID"]), song["SongName"], song["VideoURL"], song["Votes"], song["ArtistID"]); };
        div.appendChild(newButton);
    });

}

let comp = 1;
var currentSongID = 0;
var currentArtistID = 0;
songList();