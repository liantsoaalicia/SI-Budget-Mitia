// Modal de suppression produit
const modal = document.getElementById("supprimer-modal");
const closeModal = document.querySelector(".close");
const confirmDeleteBtn = document.getElementById("login-submit");

let currentProductId = null; // Stocke l'ID du produit à supprimer
let currentProductName = ''; // Nom du produit

// Ajouter un événement à tous les boutons de suppression
const deleteButtons = document.querySelectorAll(".delete-btn");
deleteButtons.forEach((button) => {
    button.addEventListener("click", (e) => {
        currentProductId = e.target.getAttribute("data-product-id");
        currentProductName = e.target.getAttribute("data-product-name"); // Récupérer le nom du produit
        document.getElementById("product-name").textContent = `Produit : ${currentProductName}`; // Mettre à jour le nom du produit dans le modal
        document.getElementById("idProduit-input").value = currentProductId; // Mettre à jour l'ID du produit dans le formulaire
        modal.style.display = "flex";
        setTimeout(() => {
            modalProduct.classList.add("show");
        }, 10);
    });
});

// Fermer le modal
closeModal.addEventListener("click", () => {
    modal.classList.remove("show");
    setTimeout(() => {
        modal.style.display = "none";
    }, 500);
});

// Confirmer la suppression
confirmDeleteBtn.addEventListener("click", () => {
    if (currentProductId) {
        // Suppression logique ici (par exemple, suppression du produit via une requête AJAX ou redirection)
        console.log(`Produit avec l'ID ${currentProductId} supprimé.`);
    }
    // Fermer le modal
    modal.classList.remove("show");
    setTimeout(() => {
        modal.style.display = "none";
    }, 500);
});

// Fermer en cliquant en dehors du modal
window.addEventListener("click", (event) => {
    if (event.target === modal) {
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
        }, 500);
    }
});

// Modal de suppression catégorie
const modal1 = document.getElementById("delcat-modal");
const closeModal1 = document.querySelector("#delcat-modal .close");
const confirmDeleteBtn1 = document.getElementById("confirm-delcat");

let currentCategoryId = null; // Stocke l'ID de la catégorie à supprimer
let currentCategoryName = ''; // Nom de la catégorie

// Ajouter un événement à tous les boutons de suppression
const deleteButtons1 = document.querySelectorAll(".delete1-btn");
deleteButtonsCategory.forEach((button) => {
    button.addEventListener("click", (e) => {
        currentCategoryId = e.target.getAttribute("data-id");
        currentCategoryName = e.target.getAttribute("data-category-name"); // Récupérer le nom de la catégorie
        document.getElementById("category-name").textContent = `Catégorie : ${currentCategoryName}`; // Mettre à jour le nom de la catégorie dans le modal
        document.getElementById("idCategorie-input").value = currentCategoryId; // Mettre à jour l'ID de la catégorie dans le formulaire
        modal.style.display = "flex";
        setTimeout(() => {
            modalCategory.classList.add("show");
        }, 10);
    });
});

// Fermer le modal
closeModal1.addEventListener("click", () => {
    modal1.classList.remove("show");
    setTimeout(() => {
        modal1.style.display = "none";
    }, 500);
});

// Confirmer la suppression
confirmDeleteBtn1.addEventListener("click", () => {
    if (currentCategoryId) {
        // Suppression logique ici (par exemple, suppression de la catégorie via une requête AJAX ou redirection)
        console.log(`Catégorie avec l'ID ${currentCategoryId} supprimée.`);
    }
    // Fermer le modal
    modal1.classList.remove("show");
    setTimeout(() => {
        modal1.style.display = "none";
    }, 500);
});

// Fermer en cliquant en dehors du modal
window.addEventListener("click", (event) => {
    if (event.target === modal1) {
        modal1.classList.remove("show");
        setTimeout(() => {
            modal1.style.display = "none";
        }, 500);
    }
});
