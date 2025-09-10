const params = new URLSearchParams(window.location.search);
const movieId = params.get('id');

// === Fonction pour extraire une couleur dominante et l'appliquer ===
function setBackgroundFromImage(imageUrl) {
  const img = new Image();
  img.crossOrigin = "Anonymous"; // TMDB autorise si "Anonymous"
  img.src = imageUrl;

  img.onload = () => {
    const colorThief = new ColorThief();
    const color = colorThief.getColor(img); // [r, g, b]

    // 🎨 Dégradé fluide
    document.body.style.background = `linear-gradient(
  270deg,
  rgba(${color[0]}, ${color[1]}, ${color[2]}, 0.9),
  rgba(0,0,0,0.95)
)`;
  };
}

// === Charger le film ===
if (movieId) {
  fetch(`https://api.themoviedb.org/3/movie/${movieId}?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR`)
    .then(res => res.json())
    .then(movie => {
      // Remplir infos
      document.getElementsByClassName('movie-header')[0].innerHTML = `<h1>${movie.title}</h1>`;
      document.getElementById('movie-details').innerHTML = `
        <div class="movie-info">
          <img src="https://image.tmdb.org/t/p/w300${movie.poster_path}" class="movie-poster" alt="${movie.title}">
        </div>
      `;


        document.getElementById('movie-details2').innerHTML = `
    <div class="movie-info">
      <h2>Informations supplémentaires</h2>
      <p><strong>Date de sortie :</strong> ${movie.release_date}</p>
      <p><strong>Durée :</strong> ${movie.runtime} minutes</p>
      <p><strong>Genre :</strong> ${movie.genres.map(g => g.name).join(', ')}</p>
      <p><strong>Résumé :</strong> ${movie.overview || "Pas de résumé disponible."}</p>
      <p><strong>Note moyenne :</strong> ${movie.vote_average} / 10 (${movie.vote_count} votes)</p>
      <p><strong>Langue originale :</strong> ${movie.original_language}</p>
      <div class="interactivebutton">
        <button type="button" id="reviewFormBtn">Noter le film</button>
        <button type="button" id="reviewListBtn">Voir tous les avis</button>
      </div>
    </div>
  `;

        const reviewListUrl = '{{ path("allreviewByMovie", {"movieId": "MOVIE_ID"}) }}';

        // Maintenant, on injecte les bonnes URLs
        document.getElementById('reviewFormBtn').onclick = () => {
            window.location.href = reviewFormUrl.replace('MOVIE_ID', movie.id);
        };

        document.getElementById('reviewListBtn').onclick = () => {
            window.location.href = reviewListUrl.replace('MOVIE_ID', movie.id);
        };
      // 🎨 Applique la couleur de la bannière
      if (movie.backdrop_path) {
        const bannerUrl = `https://image.tmdb.org/t/p/w780${movie.backdrop_path}`;
        setBackgroundFromImage(bannerUrl);
      }

      // 🎬 Récupère la bande-annonce
      fetch(`https://api.themoviedb.org/3/movie/${movieId}/videos?api_key=21a926ccdfd5aeacf7922ea1912786a2`)
        .then(res => res.json())
        .then(data => {
          const trailer = data.results.find(video =>
            video.site === "YouTube" && (video.type === "Trailer" || video.type === "Teaser")
          );

          if (trailer) {
            document.getElementById('movie-trailer').innerHTML = `
              <h2>Bande-annonce :</h2>
              <iframe width="560" height="315"
                src="https://www.youtube.com/embed/${trailer.key}"
                frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
              </iframe>
            `;
          } else {
            document.getElementById('movie-trailer').innerHTML = `<p>Aucune vidéo disponible pour ce film.</p>`;
          }
        });
    });
}
