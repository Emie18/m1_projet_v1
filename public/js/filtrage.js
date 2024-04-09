/**************************************************************************
* Nom du fichier: filtrage.js
* Description: script pour réaliser le filtrage des stages
* Auteurs: Emilie Le Rouzic, Thibault Tanné
* Date de création: avril 2024
* Version: 1.0
**************************************************************************/
//fonction qui filtre le tableau des stages  
function filterResults() {
    //récupération des valeurs d'entrées du formulaire
    //var nom = document.getElementById('nameId').value;
    var nom = document.getElementById("inputNom").value
    var groupe = document.getElementById('groupeInput').value;
    var etat_stage = document.getElementById('etatInput').value;
    var annee = document.getElementById('anneeInput').value;
    var professeur = document.getElementById('inputProf').value;
    //création de l'url contenant les variables
    var url = '/filtrage?nom=' + nom + '&groupe=' + groupe + '&etat_stage=' + etat_stage + '&annee=' + annee + '&professeur=' + professeur;
    //création de la requête
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        //si la requête est un succès alors modification du tableau des stages
        if (this.readyState == 4 && this.status == 200) {
            var tableBody = document.getElementById("tableBody");
            tableBody.innerHTML = this.responseText;
            // Utilisez le tableau principal pour la pagination
            paginer(1, true); 
        }
    };
    //envoi de la requête avec l'url
    xhttp.open("GET", url, true);
    xhttp.send();
  }
  //fonction qui filtre des noms, prénom et/ou entreprise dans les pages correspondante
  function filterResultsIndi(){
    var nom = document.getElementById("Nom");
    if(nom) nom = nom.value;
    var prenom = document.getElementById("Prenom");
    if(prenom) prenom = prenom.value
    switch(window.location.pathname){
      case "/back/apprenant":
        var url = "/filtrageSolo?part=apprenant" + "&nom=" + nom + "&prenom=" + prenom;
        break;
      case "/back/tuteur-isen":
        var url = "/filtrageSolo?part=tuteurIsen" + "&nom=" + nom + "&prenom=" + prenom;
        break;
      case "/back/tuteur-stage":
        var url = "/filtrageSolo?part=tuteurStage" + "&nom=" + nom + "&prenom=" + prenom;
        break;
      case "/back/entreprise":
        var url = "/filtrageSolo?part=entreprise" + "&nom=" + nom;
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function(){
      if (this.readyState == 4 && this.status == 200) {
        var tableBody = document.getElementById("tableBody");
        tableBody.innerHTML = this.responseText;
        paginer(1, true); // Utilisez le tableau principal pour la pagination
      }
    }
    xhttp.open("GET", url, true);
    xhttp.send();
  }
  //fonction qui permet de défiltrer le tableau des stages
  function defiltrer() {
      //mettre à vide les différentes variable
      var nom = "";
      var groupe = "";
      var etat_stage = "";
      var annee = "";
      var professeur ="";

      if(window.location.pathname == "/filtrage"){

      }else{
        switch(window.location.pathname){
          case "/back/": case "/":
            var url = '/filtrage?nom=&groupe=&etat_stage=&annee=&professeur=';
            document.getElementById('inputNom').value ="";
            document.getElementById('groupeInput').value = "";
            document.getElementById("groupeInput").value = "";
            document.getElementById('etatInput').value = "";
            document.getElementById('anneeInput').value = "";
            document.getElementById('inputProf').value = "";
            break;
          case "/back/tuteur-isen":
            var url = "/filtrageSolo?part=tuteurIsen" + "&nom=" + "&prenom=";
            document.getElementById("Nom").value = "";
            document.getElementById("Prenom").value = "";
            break;
          case "/back/tuteur-stage":
            var url = "/filtrageSolo?part=tuteurStage" + "&nom=" + "&prenom=";
            document.getElementById("Nom").value = "";
            document.getElementById("Prenom").value = "";
            break;
          case "/back/apprenant":
            var url = "/filtrageSolo?part=apprenant" + "&nom=" + "&prenom=";
            document.getElementById("Nom").value = "";
            document.getElementById("Prenom").value = "";
            break;
          case "/back/entreprise":
            var url = "/filtrageSolo?part=entreprise" + "&nom=" ;
            document.getElementById("Nom").value = "";
            break;
        }
        
      }
      //envoi de la requête pour afficher toutes les stages
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
              var tableBody = document.getElementById("tableBody");
              tableBody.innerHTML = this.responseText;
              paginer(1, true); // Utilisez le tableau principal pour la pagination
          }
      };
      console.log(url)
      xhttp.open("GET", url, true);
      xhttp.send();

  }
  //fonction pour afficher la fiche détail d'un stage
  function afficherDetails(id) {
    // Envoi d'une requête AJAX pour récupérer les détails du stage
    var xhr = new XMLHttpRequest();
    var url = '/fichedetail?id=' + id;
  
    xhr.open('GET', url, true);
    xhr.onload = function() {
      if (xhr.status >= 200 && xhr.status < 400) {
        // Succès de la requête
        var detailsDiv = document.getElementById('detailsDiv');
        detailsDiv.innerHTML = xhr.responseText; // Injecter le HTML directement dans le div
        detailsDiv.style.display = 'block'; // Afficher le div
  
        // Ajouter la classe 'active' à tous les éléments avec la classe 'fichedetail'
        setTimeout(function() {
          var details = document.getElementsByClassName('fichedetail');
          for (var i = 0; i < details.length; i++) {
            details[i].classList.add('active');
          }
          
          // Calculer la position actuelle de la fenêtre
          var windowPosition = window.scrollY;
  
          // Ajuster la position de la boîte de détails
          detailsDiv.style.top = (windowPosition + 60) + 'px'; // Ajoutez une marge de 100 pixels par exemple
        }, 10);
      }
    };
    xhr.send();
  }
  
  
  // Fonction pour fermer la fiche détail
  function fermerDetails() {
      var details = document.getElementsByClassName('fichedetail');
          for (var i = 0; i < details.length; i++) {
          details[i].classList.remove('active');
          }
           setTimeout(function() {
    var detailsDiv = document.getElementById('detailsDiv');
    detailsDiv.style.display = 'none';},200);
  
    
  
  }
  //fonction pour trier les stages par colones
  function trier(colonne) {
      // Récupérer le tableau
      var tableau = document.getElementById("tableBody");
      // Récupérer les lignes du tableau
      var lignes = tableau.getElementsByTagName("tr");
      var ordreAlphabetique = true;
      var bouton = event.target;
      // Vérifier l'ordre actuel
      var span = bouton.querySelector('span');
      if (span.innerText === "▲") {
          span.innerText = "▼";
          ordreAlphabetique = 1;
      } else {
          span.innerText = "▲";
          ordreAlphabetique = 2;
      }
      var nom = document.getElementById('nameId').value;
      var nom = nomInput ? nomInput.value : '';
      // Envoyer une requête AJAX
      var xhr = new XMLHttpRequest();
      xhr.open("GET", "/trier/" + colonne + "?desc=" + ordreAlphabetique+"?apprenant="+ nom, true);
      //console.log("/trier/" + colonne + "?desc=" + ordreAlphabetique+"?apprenant="+ nom);
      xhr.onreadystatechange = function() {
          if (xhr.readyState == XMLHttpRequest.DONE) {
              if (xhr.status == 200) {
                  tableau.innerHTML = xhr.responseText;
              } else {
                  console.error("Une erreur est survenue : " + xhr.status);
              }
          }
      };
      xhr.send();
  }
  