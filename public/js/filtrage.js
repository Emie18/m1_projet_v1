
  function filterResults() {
      var nom = document.getElementById('nameId').value;
      var groupe = document.getElementById('groupeId').value;
      var etat_stage = document.getElementById('etatInput').value;
      var annee = document.getElementById('anneeInput').value;
      var professeur = document.getElementById('profId').value;
      // console.log(nom);
      // console.log(groupe);
      // console.log(etat_stage);
      // console.log(annee);
      // console.log(professeur);
  
      var url = '/filtrage?nom=' + nom + '&groupe=' + groupe + '&etat_stage=' + etat_stage + '&annee=' + annee + '&professeur=' + professeur;
      //console.log(url)
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
              var tableBody = document.getElementById("tableBody");
              tableBody.innerHTML = this.responseText;
              paginer(1, true); // Utilisez le tableau principal pour la pagination
          }
      };
      xhttp.open("GET", url, true);
      xhttp.send();
  }
  function defiltrer() {
      var nom = "";
      var groupe = "";
      var etat_stage = "";
      var annee = "";
      var professeur ="";
    
  
      var url = '/filtrage?nom=' + nom + '&groupe=' + groupe + '&etat_stage=' + etat_stage + '&annee=' + annee + '&professeur=' + professeur;
  
      var xhttp = new XMLHttpRequest();
      xhttp.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
              var tableBody = document.getElementById("tableBody");
              tableBody.innerHTML = this.responseText;
              paginer(1, true); // Utilisez le tableau principal pour la pagination
          }
      };
      xhttp.open("GET", url, true);
      xhttp.send();
    document.getElementById('inputNom').value ="";
      document.getElementById("nameId").value = "";
      document.getElementById('inputGroupe').value = "";
      document.getElementById("groupeId").value = "";
      document.getElementById('etatInput').value = "";
      document.getElementById('anneeInput').value = "";
      document.getElementById('inputProf').value = "";
      document.getElementById("profId").value = "";
  }
  
  
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
  
  
  // Fonction pour fermer le div des détails
  function fermerDetails() {
      var details = document.getElementsByClassName('fichedetail');
          for (var i = 0; i < details.length; i++) {
          details[i].classList.remove('active');
          }
           setTimeout(function() {
    var detailsDiv = document.getElementById('detailsDiv');
    detailsDiv.style.display = 'none';},200);
  
    
  
  }
  
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
      console.log("/trier/" + colonne + "?desc=" + ordreAlphabetique+"?apprenant="+ nom);
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
  