/**************************************************************************
* Nom du fichier: trier.js
* Description: Script qui contient la fonction de tri des stages
* Auteurs: Emilie Le Rouzic, Thibault Tanné
* Date de création: avril 2024
* Version: 1.0
**************************************************************************/
//focntion de tri des stages
function trier(colonne) {
    // Récupérer la valeur de l'élément nomInput
    var nomInput = document.getElementById('inputNom');
    var nom = nomInput ? nomInput.value : '';
    var professeur = document.getElementById('inputProf').value;
    var groupe = document.getElementById('groupeInput').value;
    // Récupérer le tableau
    var tableau = document.getElementById("tableBody");
    var annee = document.getElementById('anneeInput').value;
    var etat = document.getElementById('etatInput').value;
    // Récupérer les lignes du tableau
    var lignes = tableau.getElementsByTagName("tr");
    var ordreAlphabetique = 1;

   var span =document.getElementById("s"+colonne);
    if (span.innerHTML === "▲"){
        span.innerText = "▼";
        ordreAlphabetique = 1;
    } else {
        span.innerText = "▲";
        ordreAlphabetique = 2;
    }
    // Envoyer une requête AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "/trier/" + colonne + "?desc=" + ordreAlphabetique + "&apprenant=" + nom+"&tuteur="+professeur+"&annee="+annee+ '&groupe=' + groupe+ '&etat=' + etat, true);
    console.log("/trier/" + colonne + "?desc=" + ordreAlphabetique + "&apprenant=" + nom+"&tuteur="+professeur+"&annee="+annee+ '&groupe=' + groupe+ '&etat=' + etat);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == XMLHttpRequest.DONE) {
            if (xhr.status == 200) {
                tableau.innerHTML = xhr.responseText;
                paginer(1, true); // Utilisez le tableau principal pour la pagination
            } else {
                console.error("Une erreur est survenue : " + xhr.status);
            }
        }
    };
    xhr.send();
}
