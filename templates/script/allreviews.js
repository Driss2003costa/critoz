if (movieId !== null) {
    fetch(`https://api.themoviedb.org/3/movie/${movieId}?api_key=21a926ccdfd5aeacf7922ea1912786a2&language=fr-FR`)
        .then(res => res.json())
        .then(data => {
            const backdropUrl = data.backdrop_path
                ? `https://image.tmdb.org/t/p/w1280${data.backdrop_path}`
                : null; // fallback si pas d'image
            console.log("TMDB ID :", movieId);

            document.querySelectorAll('.review').forEach(div => {
                if (backdropUrl) {
                    div.style.backgroundImage = `url('${backdropUrl}')`;
                } else {
                    div.style.backgroundColor = bannerColor;
                }

                div.style.backgroundSize = "cover";
                div.style.backgroundPosition = "center";
                div.style.color = "white";
                div.style.padding = "20px";
                div.style.borderRadius = "10px";
                div.style.textShadow = "1px 1px 4px rgba(0,0,0,0.8)";
                div.style.backdropFilter = "blur(5px)";
            });
        })
        .catch(err => {
            console.error("Erreur lors de la récupération du film TMDB :", err);
        });
} else {
    document.querySelectorAll('.review').forEach(div => {
        div.style.backgroundColor = bannerColor || "#969696ff";
        div.style.color = "white";
        div.style.padding = "20px";
        div.style.borderRadius = "10px";
        div.style.textShadow = "1px 1px 4px rgba(0,0,0,0.8)";
    });
}
