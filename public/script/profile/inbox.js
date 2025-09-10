function loadMessage(id) {
    fetch("/message/" + id)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                document.getElementById("message-content").innerHTML = "<p>" + data.error + "</p>";
            } else {
                document.getElementById("message-content").innerHTML = `
  <div class="message-card">
    <h2 class="message-title">${data.subject}</h2>
    <p class="message-sender"><strong>De :</strong> ${data.sender}</p>
    <div class="message-body">${data.body}</div>
  </div>
`;
            }
        })
        .catch(error => {
            console.error("Erreur:", error);
            document.getElementById("message-content").innerHTML = "<p>Impossible de charger le message.</p>";
        });
}
function openComposeModal() {
    document.getElementById("composeModal").classList.remove("hidden");
}
function closeComposeModal() {
    document.getElementById("composeModal").classList.add("hidden");
}