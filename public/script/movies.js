let currentPage = 1;
let currentGenreId = null;
let nextPage = document.getElementById('next');
let prevPage = document.getElementById('prev');
const searchbutton = document.getElementById('search');
const searchinput = document.getElementById('searchinput');

let filteroptions = document.querySelector('.filter-options');
let filterDropdown = document.querySelector('.filter-dropdown');

let alphabeticalAsc = true; // état actuel (true = A→Z, false = Z→A)
let dateRecentFirst = true; // état actuel (true = récents d'abord, false = anciens d'abord)
let sortMode = "release_date.asc";


const toggleAlphabetical = document.getElementById('toggleAlphabetical');
const toggleDate = document.getElementById('toggleDate');


function fetchMovies(page = 1) {
  fetch(`https://api.themoviedb.org/3/discover/movie?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR&sort_by=${sortMode}&page=${page}`)
    .then(res => res.json())
    .then(data => {
      displayMovies(data.results);
      document.getElementById('page-number').textContent = `Page ${page} / ${data.total_pages}`;
    });
}

function displayMovies(movies) {
  const container = document.getElementById('film-container');
  container.innerHTML = '';
  movies.forEach(movie => {
    const div = document.createElement('div');
    div.innerHTML = `
      <h3>${movie.title} (${movie.release_date})</h3>
      <img src="https://image.tmdb.org/t/p/w200${movie.poster_path}" alt="${movie.title}">
    `;
    container.appendChild(div);
  });
}

//============= Boutons suivant / précédent==========================
document.getElementById('next').addEventListener('click', () => {
  currentPage++;
  fetchMovies(currentPage);
});

document.getElementById('prev').addEventListener('click', () => {
  if (currentPage > 1) {
    currentPage--;
    fetchMovies(currentPage);
  }
});
//===================================================================



//====================  Bouton bascule ancien / récent==========================
document.getElementById('toggleDate').addEventListener('click', () => {
  if (sortMode === "release_date.asc") {
    sortMode = "release_date.desc"; // plus récents
    document.getElementById('toggleDate').textContent = "Voir les plus anciens";
  } else {
    sortMode = "release_date.asc"; // plus anciens
    document.getElementById('toggleDate').textContent = "Voir les plus récents";
  }
  currentPage = 1;
  fetchMovies(currentPage);
});

//================================================================================




//============================ Centre de tri ====================================
function applyFilter(filterType) {
  fetch(`https://api.themoviedb.org/3/movie/now_playing?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR&page=${currentPage}`)
    .then(res => res.json())
    .then(data => {
      let movies = data.results;

      if (filterType === 'alphabetical') {
        if (alphabeticalAsc) {
          movies.sort((a, b) => a.title.localeCompare(b.title)); // A→Z
          toggleAlphabetical.textContent = "Z ➡️ A"; // changer le texte
        } else {
          movies.sort((a, b) => b.title.localeCompare(a.title)); // Z→A
          toggleAlphabetical.textContent = "A ➡️ Z";
        }
        alphabeticalAsc = !alphabeticalAsc; // inverse l'état
      }

      if (filterType === 'date') {
        if (dateRecentFirst) {
          movies.sort((a, b) => new Date(b.release_date) - new Date(a.release_date)); // récents d'abord
          toggleDate.textContent = "Plus anciens";
        } else {
          movies.sort((a, b) => new Date(a.release_date) - new Date(b.release_date)); // anciens d'abord
          toggleDate.textContent = "Plus récents";
        }
        dateRecentFirst = !dateRecentFirst; // inverse l'état
      }

      displayMovies(movies);
    });
}
//==============================================================================================


//==================== Événements des boutons de tri ============================================
toggleAlphabetical.addEventListener('click', () => applyFilter('alphabetical'));
toggleDate.addEventListener('click', () => applyFilter('date'));


const genreButtons = {
  action: 28,
  horror: 27,
  scifi: 878,
  thriller: 53,
  comedy: 35,
  war: 10752,
  family: 10751,
  romance: 10749,
  fantasy: 14,
  animation: 16
};

function fetchMovies(page = 1, genreId = null) {
  let url = genreId
    ? `https://api.themoviedb.org/3/discover/movie?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR&with_genres=${genreId}&page=${page}`
    : `https://api.themoviedb.org/3/movie/now_playing?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR&page=${page}`;

  fetch(url)
    .then(res => res.json())
    .then(data => {
      displayMovies(data.results);
      document.getElementById('page-number').textContent = `Page ${page}`;
    });
}

function displayMovies(movies) {
  const container = document.getElementById('film-container');
  container.innerHTML = '';
  movies.forEach(movie => {
    const button = document.createElement('button');
    button.innerHTML = `
      <h3>${movie.title}</h3>
      <img src="https://image.tmdb.org/t/p/w200${movie.poster_path}" alt="${movie.title}">
    `;
    button.style.backgroundColor = 'transparent';
    button.style.border = 'none';
    button.style.padding = '0';
    button.style.cursor = 'pointer';
    button.onmouseover = () => {
      button.style.opacity = '0.8';
      button.style.transform = 'scale(1.05)';
      button.style.transition = 'transform 0.3s, opacity 0.3s';
      button.onclick = () => {
        window.location.href = reviewFormBase.replace('MOVIE_ID', movie.id);
      };
    };
    button.onmouseout = () => {
      button.style.opacity = '1';
      button.style.transform = 'scale(1)';
    };
    container.appendChild(button);
  });
  updatePrevVisibility();

}


document.createElement('div');
filteroptions.addEventListener('click', () => {
  filterDropdown.style.display = filterDropdown.style.display === 'flex' ? 'none' : 'flex';
});




document.getElementById('next').addEventListener('click', () => {
  currentPage++;
  fetchMovies(currentPage, currentGenreId);
  updatePrevVisibility();
});

document.getElementById('prev').addEventListener('click', () => {
  if (currentPage > 1) {
    currentPage--;
    fetchMovies(currentPage, currentGenreId);
    updatePrevVisibility();
  }
});


Object.keys(genreButtons).forEach(id => {
  const button = document.getElementById(id);
  if (button) {
    button.addEventListener('click', () => {
      currentGenreId = genreButtons[id];
      currentPage = 1;
      fetchMovies(currentPage, currentGenreId);
      updatePrevVisibility();
    });
  }
});

if (currentPage === 1) {
  fetchMovies();
}

function updatePrevVisibility() {
  if (currentPage === 1) {
    prevPage.style.opacity = '0';
    prevPage.style.pointerEvents = 'none';
  } else {
    prevPage.style.opacity = '1';
    prevPage.style.pointerEvents = 'auto';
  }
}


searchbutton.addEventListener('click', () => {
  const query = searchinput.value.trim();
  if (query.length > 0) {
    searchMovies(query);
  }
});

function searchMovies(query) {
  fetch(`https://api.themoviedb.org/3/search/movie?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR&query=${encodeURIComponent(query)}`)
    .then(res => res.json())
    .then(data => {
      displayMovies(data.results);
      document.getElementById('page-number').textContent = `Résultats pour : "${query}"`;
      currentGenreId = null;
      currentPage = 1;
      updatePrevVisibility();
    });
}