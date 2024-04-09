/**************************************************************************
* Nom du fichier: pagination.js
* Description: Script pour réaliser la pagination des tableaux
* Auteurs: Emilie Le Rouzic, Thibault Tanné
* Date de création: avril 2024
* Version: 1.0
**************************************************************************/
function paginer(page) {
    var itemsPerPage = 30; // Nombre d'éléments par page
    var tableBody = document.getElementById("tableBody");
    var rows = tableBody.getElementsByTagName("tr");

    var startIndex = (page - 1) * itemsPerPage;
    var endIndex = startIndex + itemsPerPage;

    for (var i = 0; i < rows.length; i++) {
        if (i >= startIndex && i < endIndex) {
            rows[i].style.display = "";
        } else {
            rows[i].style.display = "none";
        }
    }

    // Mettre à jour les boutons de pagination
    var totalPages = Math.ceil(rows.length / itemsPerPage);
    var paginationButtons = document.getElementById("paginationButtons");

    // Effacer les boutons de pagination existants
    paginationButtons.innerHTML = '';

    // Bouton précédent
    if (page > 1) {
        paginationButtons.innerHTML += '<button onclick="paginer(' + (page - 1) + ')">Page précédente</button>';
    }

    // Liens vers chaque page
    var maxVisiblePages = 20; // Nombre maximal de boutons de page visibles
    var startPage = Math.max(1, page - Math.floor(maxVisiblePages / 2));
    var endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

    if (startPage > 1) {
        paginationButtons.innerHTML += '<button onclick="paginer(1)">1</button>';
        if (startPage > 2) {
            paginationButtons.innerHTML += '<span>...</span>';
        }
    }

    for (var i = startPage; i <= endPage; i++) {
        paginationButtons.innerHTML += '<button onclick="paginer(' + i + ')"' + (i === page ? ' class="active"' : '') + '>' + i + '</button>';
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationButtons.innerHTML += '<span>...</span>';
        }
        paginationButtons.innerHTML += '<button onclick="paginer(' + totalPages + ')">' + totalPages + '</button>';
    }

    // Bouton suivant
    if (page < totalPages) {
        paginationButtons.innerHTML += '<button onclick="paginer(' + (page + 1) + ')">Page suivante</button>';
    }
}
function addMore(){
    var liste = document.getElementById("listTuteur")
    var xhr = new XMLHttpRequest();
    xhr.open("GET", )
}