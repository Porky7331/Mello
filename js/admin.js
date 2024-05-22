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

async function addSong(){
    console.log("add song");
    let response = await fetchData("../admin/requesthandler.php", {"addSong":comp});
    console.log(response);
}

async function songList(){
    let div = document.getElementsByClassName("songListDiv")[0];
    div.inneHTML = "";
    
    let response = await fetchData("../admin/requesthandler.php", {"getCompSongs":comp});
    let songs = JSON.parse(response);
    songs.forEach(song => {
        console.log(song["ID"], song["SongName"]);
        let newButton = document.createElement('button');
        newButton.innerHTML = song["SongName"];
        newButton.onclick = function () { editSong(parseInt(song["ID"])); };
        div.appendChild(newButton);
    });

}

let comp = 1;
songList();