// Sélection des éléments
const modal = document.getElementById("admin-modal");
const openModalBtn = document.getElementById("open-admin-modal");
const closeModal = document.querySelector(".close");

// Ouvrir le modal avec animation
openModalBtn.addEventListener("click", () => {
    modal.style.display = "flex"; // Nécessaire pour initier l'affichage
    setTimeout(() => {
        modal.classList.add("show"); // Ajouter la classe après un délai pour activer la transition
    }, 10);
});

// Fermer le modal avec animation
closeModal.addEventListener("click", () => {
    modal.classList.remove("show"); // Supprime la classe pour inverser l'animation
    setTimeout(() => {
        modal.style.display = "none"; // Cache complètement après l'animation
    }, 500); // Doit correspondre à la durée de la transition CSS (1s)
});

// Fermer le modal en cliquant en dehors
window.addEventListener("click", (event) => {
    if (event.target === modal) {
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
        }, 1000);
    }
});

