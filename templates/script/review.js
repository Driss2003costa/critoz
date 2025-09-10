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
      document.getElementsByClassName('movie-header')[0].innerHTML = 
      `<h1>Votre review pour le film: ${movie.title}</h1></br>`;

        const posterUrl = movie.poster_path
            ? `https://image.tmdb.org/t/p/w300${movie.poster_path}`
            : "{{ asset('img/no-poster.png') }}"; // 🔄 Image de fallback que tu ajoutes dans /public/img/
        document.getElementById('movie-details').innerHTML = `
        <div class="movie-info">
          <img src="https://image.tmdb.org/t/p/w300${movie.poster_path}" class="movie-poster" alt="${movie.title}">
        </div>
      `;
      document.getElementById('movie-details3').innerHTML = `
        <div class="movie-info">
          <div class="reviewtext flex-center">
            <form id="review-form" class="flex-column" action="{{ path('reviews_submit', {'movieId': movieId}) }}" method="post">
                <input type="hidden" name="movie_id" value="${movieId}">
                <input type="hidden" name="movie_title" value="${movie.title}">
                <input type="text" id="titlereview" name="titlereview" placeholder="Titre">
                <textarea id="reviewarea" name="reviews" placeholder="Mettez votre review"></textarea>
                <div class="rating flex-column">
                <input type="hidden" name="rating" id="ratinginput">
                	<div class="stars">
                		<span class="fa fa-star">★</span>
                		<span class="fa fa-star">★</span>
                		<span class="fa fa-star">★</span>
                		<span class="fa fa-star">★</span>
                		<span class="fa fa-star">★</span>
                	</div>
                </div>
                <button type="submit" form="review-form" value="Submit">Envoyer</button>
            </form>
          </div>
        </div>
      `;
        document.getElementById('review-form').action = reviewFormUrl.replace('MOVIE_ID', movieId);
        console.log(movie)

      const stars = document.querySelectorAll('.fa-star');
      const ratingInput = document.getElementById('ratinginput');
        stars.forEach((star, index) => {
        star.addEventListener('click', () => {
            stars.forEach((s, i) => {
            s.classList.toggle('gold', i <= index);
            });
            ratingInput.value = (index + 1) * 2;
            console.log("Note sélectionnée :", ratingInput.value);
            stars.innerHTML = ratingInput.value;
        });
    });


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
