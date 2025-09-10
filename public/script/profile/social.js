// ----------------------- Variables -----------------------
const CURRENT_USER_ID = parseInt(document.getElementById('current-user-id').value);
const messageContent = document.getElementById("message-content");
let currentConversationId = null;

// ----------------------- WebSocket -----------------------
const ws = new WebSocket("ws://localhost:8080");

ws.onopen = () => console.log("WebSocket connecté");
ws.onclose = () => console.log("WebSocket fermé");

ws.onmessage = function(event) {
    try {
        const data = JSON.parse(event.data);
        if (data.conversation_id !== currentConversationId) return;

        const div = document.createElement('div');
        div.classList.add('message-card');
        div.innerHTML = `
            <img src="${data.sender_pp || '/img/default_pp.png'}" 
                 width="40" height="40" style="border-radius:50%; margin-right:10px;">
            <strong>${data.sender}</strong>: ${data.body}
        `;
        messageContent.appendChild(div);
        messageContent.scrollTop = messageContent.scrollHeight;
    } catch (err) {
        console.error("Erreur lors du traitement du message WS :", err);
    }
};

// ----------------------- Fonctions globales pour le modal -----------------------
window.openComposeModal = function(type = null) {
    const modal = document.getElementById('composeModal');
    modal.classList.remove('hidden');

    const receiver = document.getElementById('receiver');
    if (type === 'friends') receiver.placeholder = "Nom d'ami";
    else if (type === 'support') receiver.placeholder = "Support";
    else receiver.placeholder = "Destinataire";
}

window.closeComposeModal = function() {
    const modal = document.getElementById('composeModal');
    modal.classList.add('hidden');
    document.getElementById('composeForm').reset();
}

// ----------------------- Gestion du clic sur le bouton close -----------------------
document.querySelector('#composeModal .close').addEventListener('click', () => closeComposeModal());

// ----------------------- Envoi d'un message -----------------------
document.getElementById('composeForm').onsubmit = async function(e) {
    e.preventDefault();

    const receiver = document.getElementById('receiver').value;
    const subject = document.getElementById('subject').value;
    const body = document.getElementById('body').value;

    if (!receiver || !body) return alert("Remplissez tous les champs");

    let conversationId = currentConversationId;

    // Créer conversation si elle n'existe pas
    if (!conversationId) {
        try {
            const res = await fetch('/conversation/create', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ receiver, subject, body })
            });
            const data = await res.json();
            if (data.error) return alert(data.error);
            conversationId = data.conversation_id;
            currentConversationId = conversationId;
        } catch (err) {
            return alert("Erreur lors de la création de conversation");
        }
    }

    // Envoyer le message via WebSocket
    const payload = {
        conversation_id: conversationId,
        sender_id: CURRENT_USER_ID,
        body
    };
    ws.send(JSON.stringify(payload));

    // Ajouter localement pour UX immédiate
    const div = document.createElement('div');
    div.classList.add('message-card');
    div.innerHTML = `<img src="${msg.sender.profile_picture || '/img/profile-user.png'}" 
                 width="40" height="40" style="border-radius:50%; margin-right:10px;">
            <strong></strong>: ${data.body}`;

    messageContent.appendChild(div);
    messageContent.scrollTop = messageContent.scrollHeight;

    // Reset formulaire et fermer modal
    document.getElementById('composeForm').reset();
    closeComposeModal();
};

// ----------------------- Charger une conversation -----------------------
function loadConversation(conversationId) {
    currentConversationId = conversationId;
    document.getElementById('conversation_id').value = conversationId;
    messageContent.innerHTML = '';

    fetch(`/conversation/${conversationId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.messages) return;
            data.messages.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('message-card');
                div.innerHTML = `
            <img src="${msg.sender.profile_picture || '/img/profile-user.png'}" 
                 width="40" height="40" style="border-radius:50%; margin-right:10px;">
            <strong></strong>: ${msg.body}
        `;
                div.innerHTML = `<strong>${msg.sender.username}</strong>: ${msg.body}`;
                messageContent.appendChild(div);
            });
            messageContent.scrollTop = messageContent.scrollHeight;
        })
        .catch(err => console.error("Erreur lors du chargement :", err));
}

// ----------------------- Clic sur messages de la sidebar -----------------------
document.querySelectorAll('.message-item').forEach(btn => {
    btn.addEventListener('click', () => {
        const conversationId = btn.dataset.conversation;
        loadConversation(conversationId);
    });
});

// ----------------------- Clic sur types de modal -----------------------
document.querySelector('.friends-button').addEventListener('click', () => openComposeModal('friends'));



document.querySelector('.support-button').addEventListener('click', () => openComposeModal('support'));

document.querySelector('.support-button').onclick = () => {
    document.querySelectorAll('.message-card').forEach(div => {
        div.style.background= "linear-gradient(135deg, #af5858,#af1a1a, #4d0000);";

    });
};
document.querySelector('.new-message-button').addEventListener('click', () => openComposeModal());

