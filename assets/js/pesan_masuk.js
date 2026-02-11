document.addEventListener('DOMContentLoaded', function () {

  const messagesContainer = document.getElementById('messagesContainer');
  const replyPopup = document.getElementById('replyPopup');
  const replyToEl = document.getElementById('replyTo');
  const replyMessageEl = document.getElementById('replyMessage');
  const sendReplyBtn = document.getElementById('sendReplyBtn');
  const cancelReplyBtn = document.getElementById('cancelReplyBtn');
  const deleteAllBtn = document.getElementById('deleteAllBtn');

  if (!messagesContainer) return;

  let conversations = [];
  let currentReplyingTo = null;
  let currentUserId = null;

  // ================= AUTH CHECK =================
  fetch("../../backend/api/auth.php?action=status")
    .then(res => res.json())
    .then(data => {
      if (!data.loggedIn) {
        messagesContainer.innerHTML = "<p class='empty-msg'>Silakan login.</p>";
        return;
      }

      currentUserId = data.data.userId;
      loadConversations();
    })
    .catch(() => {
      messagesContainer.innerHTML = "<p class='empty-msg'>Gagal cek login.</p>";
    });

  // ================= LOAD PESAN =================
  async function loadConversations() {
    try {
      const response = await fetch('../../backend/api/messages.php');
      const result = await response.json();

      if (result.status === 'success') {
        conversations = result.data;
        renderConversations();
      } else {
        throw new Error(result.message);
      }

    } catch (error) {
      console.error(error);
      messagesContainer.innerHTML = "<p class='empty-msg'>Gagal memuat pesan.</p>";
    }
  }

  // ================= RENDER PESAN =================
  function renderConversations() {
    messagesContainer.innerHTML = '';

    if (!conversations.length) {
      messagesContainer.innerHTML = "<p class='empty-msg'>Tidak ada pesan masuk.</p>";
      return;
    }

    if (deleteAllBtn) {
      deleteAllBtn.disabled = true;
      deleteAllBtn.title = "Fitur dinonaktifkan";
    }

    conversations.forEach(convo => {
      const partner = convo.partner_info;

      const unreadCount = convo.messages.filter(
        m => !m.is_read && m.receiver_id == currentUserId
      ).length;

      const card = document.createElement('div');
      card.className = 'message-card';

      card.innerHTML = `
        <div class="card-header">
          <h4>
            ${partner.name}
            ${unreadCount > 0 ? `<span class="unread-badge">${unreadCount}</span>` : ''}
          </h4>
          <button class="reply-btn"
            data-partner-id="${partner.id}"
            data-partner-name="${partner.name}">
            Balas
          </button>
        </div>

        <p><strong>Email:</strong> ${partner.email}</p>

        <div class="message-thread">
          ${convo.messages.map(msg => `
            <div class="message-bubble ${msg.sender_id == partner.id ? 'received' : 'sent'}">
              <p>${msg.message_content}</p>
              <span class="timestamp">
                ${new Date(msg.created_at).toLocaleString('id-ID')}
              </span>
            </div>
          `).join('')}
        </div>
      `;

      messagesContainer.appendChild(card);
    });

    attachReplyListeners();
  }

  // ================= REPLY =================
  function attachReplyListeners() {
    document.querySelectorAll('.reply-btn').forEach(btn => {
      btn.onclick = () => {
        currentReplyingTo = btn.dataset.partnerId;
        replyToEl.innerHTML = `<strong>Kepada:</strong> ${btn.dataset.partnerName}`;
        replyPopup.style.display = 'flex';
        replyMessageEl.focus();
      };
    });
  }

  async function sendReply() {
    const msg = replyMessageEl.value.trim();

    if (!msg || !currentReplyingTo) {
      alert("Pesan tidak boleh kosong");
      return;
    }

    try {
      const response = await fetch('../../backend/api/messages.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          receiver_id: currentReplyingTo,
          message_content: msg
        })
      });

      const result = await response.json();

      if (result.status === 'success') {
        replyPopup.style.display = 'none';
        replyMessageEl.value = '';
        currentReplyingTo = null;
        loadConversations();
      } else {
        alert(result.message);
      }

    } catch (error) {
      console.error(error);
      alert("Gagal mengirim balasan");
    }
  }

  if (sendReplyBtn) sendReplyBtn.onclick = sendReply;
  if (cancelReplyBtn) {
    cancelReplyBtn.onclick = () => {
      replyPopup.style.display = 'none';
      replyMessageEl.value = '';
      currentReplyingTo = null;
    };
  }

});
