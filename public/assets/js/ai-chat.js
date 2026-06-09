/* ═══════════════════════════════════════════
   AI Chat Widget — CargoFlow
   ═══════════════════════════════════════════ */
(function () {
  'use strict';

  /* ─── Helpers ─── */
  function escHtml(str) {
    if (!str) return '';
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  }

  /* ─── DOM refs ─── */
  const btn      = document.getElementById('ai-chat-btn');
  const panel    = document.getElementById('ai-chat-panel');
  const closeBtn = document.getElementById('ai-chat-close');
  const messages = document.getElementById('ai-chat-messages');
  const typing   = document.getElementById('ai-typing');
  const input    = document.getElementById('ai-chat-input');
  const sendBtn  = document.getElementById('ai-chat-send');

  if (!btn || !panel || !messages || !input || !sendBtn) return;

  /* ─── Toggle panel ─── */
  function openPanel() {
    panel.classList.add('ai-chat-panel--open');
    btn.classList.add('ai-chat-btn--open');
    input.focus();
  }

  function closePanel() {
    panel.classList.remove('ai-chat-panel--open');
    btn.classList.remove('ai-chat-btn--open');
  }

  btn.addEventListener('click', () => {
    if (panel.classList.contains('ai-chat-panel--open')) {
      closePanel();
    } else {
      openPanel();
    }
  });

  if (closeBtn) {
    closeBtn.addEventListener('click', closePanel);
  }

  /* ─── Render message ─── */
  function appendMessage(text, role) {
    const wrapper = document.createElement('div');
    wrapper.className = 'ai-msg ai-msg--' + role;

    const bubble = document.createElement('div');
    bubble.className = 'ai-msg__bubble';
    bubble.innerHTML = escHtml(text).replace(/\n/g, '<br>');

    wrapper.appendChild(bubble);
    messages.appendChild(wrapper);
    scrollToBottom();
  }

  function scrollToBottom() {
    messages.scrollTop = messages.scrollHeight;
  }

  /* ─── Typing indicator ─── */
  function showTyping() {
    if (typing) typing.classList.add('ai-typing--visible');
    scrollToBottom();
  }

  function hideTyping() {
    if (typing) typing.classList.remove('ai-typing--visible');
  }

  /* ─── Send message ─── */
  async function sendMessage(text) {
    text = text.trim();
    if (!text) return;

    appendMessage(text, 'user');
    input.value = '';
    input.style.height = '';
    sendBtn.disabled = true;

    // Hide quick actions after first send
    const quick = document.getElementById('ai-chat-quick');
    if (quick) quick.style.display = 'none';

    showTyping();

    try {
      const res = await fetch('/api/ai.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken(),
        },
        body: JSON.stringify({
          message: text,
          csrf_token: getCsrfToken(),
        }),
      });

      const data = await res.json();
      hideTyping();

      if (data.status === 'ok' && data.reply) {
        appendMessage(data.reply, 'ai');
      } else {
        appendMessage(data.message || 'Произошла ошибка. Попробуйте ещё раз.', 'ai');
      }
    } catch {
      hideTyping();
      appendMessage('Ошибка соединения с сервером. Попробуйте позже.', 'ai');
    }

    sendBtn.disabled = false;
    input.focus();
  }

  /* ─── Send button click ─── */
  sendBtn.addEventListener('click', () => sendMessage(input.value));

  /* ─── Enter to send (Shift+Enter = newline) ─── */
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage(input.value);
    }
  });

  /* ─── Auto-resize textarea ─── */
  input.addEventListener('input', () => {
    input.style.height = '';
    input.style.height = Math.min(input.scrollHeight, 100) + 'px';
  });

  /* ─── Quick action buttons ─── */
  document.querySelectorAll('.ai-quick-btn').forEach((qBtn) => {
    qBtn.addEventListener('click', () => {
      const text = qBtn.dataset.msg || qBtn.textContent.trim();
      sendMessage(text);
    });
  });

})();
