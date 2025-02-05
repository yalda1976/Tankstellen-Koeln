// Frontend Script - Handles search and sorting functionality

document.addEventListener("DOMContentLoaded", () => {
  // Cache DOM elements
  let cachedCards = null;
  const searchInput = document.getElementById("station-search");
  const sortSelect = document.getElementById("station-sort");
  const stationList = document.getElementById("station-list");

  // Get and cache cards
  function getCards() {
    if (!cachedCards && stationList) {
      cachedCards = Array.from(
        stationList.getElementsByClassName("station-card")
      );
    }
    return cachedCards;
  }

  // Search functionality
  if (searchInput) {
    searchInput.addEventListener("input", function () {
      const searchText = this.value.trim().toLowerCase();
      const cards = getCards();

      if (cards) {
        cards.forEach((card) => {
          const address = card
            .querySelector(".card-title")
            .textContent.toLowerCase();
          card.style.display = address.includes(searchText) ? "block" : "none";
        });
      }
    });
  }

  // Sorting functionality
  if (sortSelect) {
    sortSelect.addEventListener("change", function () {
      const order = this.value;
      const cards = getCards();

      if (cards && stationList) {
        cards.sort((a, b) => {
          const nameA = a.dataset.name.toLowerCase();
          const nameB = b.dataset.name.toLowerCase();
          return order === "asc"
            ? nameA.localeCompare(nameB)
            : nameB.localeCompare(nameA);
        });

        // Re-append sorted cards
        cards.forEach((card) => stationList.appendChild(card));
      }
    });
  }
});
