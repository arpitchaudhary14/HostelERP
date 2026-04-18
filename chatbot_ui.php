<style>
.leon-chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
}
.leon-chatbot-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-info, #00C9FF) 0%, #2a5298 100%);
    border: none;
    color: white;
    font-size: 24px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    cursor: pointer;
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}
.leon-chatbot-btn:hover {
    transform: scale(1.1);
}
.leon-chatbot-window {
    display: none;
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 500px;
    background: rgba(30, 30, 30, 0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 16px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    flex-direction: column;
    overflow: hidden;
    font-family: 'Inter', sans-serif;
    color: #fff;
    z-index: 9999;
    animation: slideUp 0.3s ease-out;
    transition: background 0.3s, color 0.3s;
}
.leon-chatbot-window.light-theme {
    background: rgba(255, 255, 255, 0.85);
    color: #333;
    border: 1px solid rgba(0, 0, 0, 0.1);
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.leon-chat-header {
    background: rgba(40, 40, 40, 0.9);
    padding: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    transition: background 0.3s;
}
.light-theme .leon-chat-header {
    background: rgba(240, 240, 240, 0.9);
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}
.leon-chat-header .title {
    display: flex;
    align-items: center;
    gap: 10px;
}
.leon-logo-svg {
    width: 28px;
    height: 28px;
    fill: url(#leonGradient);
    filter: drop-shadow(0px 2px 2px rgba(0,0,0,0.3));
}
.leon-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}
.leon-btn-icon {
    background: none;
    border: none;
    color: #aaa;
    font-size: 18px;
    cursor: pointer;
    transition: color 0.2s;
}
.light-theme .leon-btn-icon {
    color: #666;
}
.leon-btn-icon:hover {
    color: var(--accent-info, #00C9FF);
}
.leon-chat-messages {
    flex-grow: 1;
    padding: 15px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.leon-message {
    max-width: 80%;
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
}
.leon-message.user {
    align-self: flex-end;
    background: linear-gradient(135deg, var(--accent-info, #00C9FF) 0%, #92FE9D 100%);
    color: #000;
    border-bottom-right-radius: 4px;
}
.leon-message.ai {
    align-self: flex-start;
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
    border-bottom-left-radius: 4px;
}
.light-theme .leon-message.ai {
    background: rgba(0, 0, 0, 0.05);
    color: #333;
}
.leon-chat-input-area {
    padding: 10px 15px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(30, 30, 30, 0.9);
    display: flex;
    gap: 10px;
    transition: background 0.3s;
}
.light-theme .leon-chat-input-area {
    background: rgba(240, 240, 240, 0.9);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}
.leon-chat-input {
    flex-grow: 1;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    padding: 10px 15px;
    color: #fff;
    outline: none;
    transition: all 0.3s;
}
.light-theme .leon-chat-input {
    background: rgba(255, 255, 255, 0.8);
    border: 1px solid rgba(0, 0, 0, 0.1);
    color: #333;
}
.leon-chat-input::placeholder {
    color: #aaa;
}
.light-theme .leon-chat-input::placeholder {
    color: #888;
}
.leon-send-btn {
    background: linear-gradient(135deg, var(--accent-info, #00C9FF) 0%, #2a5298 100%);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform 0.2s;
}
.leon-send-btn:hover {
    transform: scale(1.05);
}
.leon-chat-footer {
    padding: 8px 10px;
    text-align: center;
    font-size: 10px;
    color: rgba(255,255,255,0.4);
    background: rgba(20, 20, 20, 0.9);
    transition: background 0.3s;
}
.light-theme .leon-chat-footer {
    background: rgba(230, 230, 230, 0.9);
    color: rgba(0,0,0,0.5);
}
.leon-typing {
    display: none;
    align-self: flex-start;
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 14px;
    border-radius: 18px;
    border-bottom-left-radius: 4px;
    gap: 4px;
}
.light-theme .leon-typing {
    background: rgba(0, 0, 0, 0.05);
}
.leon-typing span {
    width: 6px;
    height: 6px;
    background-color: #aaa;
    border-radius: 50%;
    display: inline-block;
    animation: leonBlink 1.4s infinite both;
}
.light-theme .leon-typing span {
    background-color: #666;
}
.leon-typing span:nth-child(1) { animation-delay: 0.2s; }
.leon-typing span:nth-child(2) { animation-delay: 0.4s; }
.leon-typing span:nth-child(3) { animation-delay: 0.6s; }
@keyframes leonBlink {
    0% { opacity: 0.2; transform: translateY(0); }
    20% { opacity: 1; transform: translateY(-2px); }
    100% { opacity: 0.2; transform: translateY(0); }
}
.leon-chat-messages::-webkit-scrollbar, .leon-sidebar-content::-webkit-scrollbar { width: 6px; }
.leon-chat-messages::-webkit-scrollbar-track, .leon-sidebar-content::-webkit-scrollbar-track { background: transparent; }
.leon-chat-messages::-webkit-scrollbar-thumb, .leon-sidebar-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }
.light-theme.leon-chatbot-window .leon-chat-messages::-webkit-scrollbar-thumb, 
.light-theme.leon-chatbot-window .leon-sidebar-content::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); }
.leon-chat-sidebar {
    position: absolute;
    top: 60px; 
    left: -260px;
    width: 250px;
    height: calc(100% - 60px);
    background: rgba(20, 20, 20, 0.95);
    backdrop-filter: blur(20px);
    border-right: 1px solid rgba(255, 255, 255, 0.1);
    z-index: 100;
    transition: left 0.3s ease;
    display: flex;
    flex-direction: column;
}
.leon-chat-sidebar.active {
    left: 0;
}
.light-theme .leon-chat-sidebar {
    background: rgba(245, 245, 245, 0.95);
    border-right: 1px solid rgba(0, 0, 0, 0.1);
}
.leon-sidebar-header {
    padding: 15px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.light-theme .leon-sidebar-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}
.leon-new-chat-btn {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--accent-info, #00C9FF) 0%, #2a5298 100%);
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: opacity 0.2s;
}
.leon-new-chat-btn:hover {
    opacity: 0.9;
}
.leon-sidebar-content {
    flex-grow: 1;
    overflow-y: auto;
    padding: 10px;
}
.leon-history-item {
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 8px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background 0.2s;
    font-size: 13px;
    color: #ccc;
    position: relative;
}
.light-theme .leon-history-item {
    color: #555;
}
.leon-history-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.light-theme .leon-history-item:hover {
    background: rgba(0, 0, 0, 0.05);
    color: #000;
}
.leon-history-item.active {
    background: rgba(0, 201, 255, 0.15);
    color: #00C9FF;
    border: 1px solid rgba(0, 201, 255, 0.3);
}
.leon-history-item .title-text {
    flex-grow: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-right: 10px;
}
.leon-delete-chat {
    opacity: 0;
    padding: 5px;
    border-radius: 4px;
    color: #ff4d4d;
    transition: opacity 0.2s, background 0.2s;
    cursor: pointer;
}
.leon-history-item:hover .leon-delete-chat {
    opacity: 1;
}
.leon-delete-chat:hover {
    background: rgba(255, 77, 77, 0.1);
}
.leon-sidebar-overlay {
    display: none;
    position: absolute;
    top: 60px;
    left: 0;
    width: 100%;
    height: calc(100% - 60px);
    background: rgba(0,0,0,0.3);
    z-index: 90;
}
.leon-sidebar-overlay.active {
    display: block;
}
</style>
<div class="leon-chatbot-container">
    <button class="leon-chatbot-btn" id="leonToggleBtn">
        <svg viewBox="0 0 24 24" width="28" height="28" fill="white"><path d="M20 2H4C2.9 2 2 2.9 2 4v18l4-4h14c1.1 0 2-.9 2-2V4C22 2.9 21.1 2 20 2zM9 11H7V9h2V11zM13 11h-2V9h2V11zM17 11h-2V9h2V11z"/></svg>
    </button>
    <div class="leon-chatbot-window" id="leonWindow">
        <div class="leon-chat-header">
            <div class="title">
                <svg class="leon-logo-svg" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="leonGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#00C9FF" />
                            <stop offset="100%" stop-color="#92FE9D" />
                        </linearGradient>
                    </defs>
                    <rect x="12" y="20" width="40" height="32" rx="8" />
                    <circle cx="24" cy="34" r="4" fill="#1e1e1e" />
                    <circle cx="40" cy="34" r="4" fill="#1e1e1e" />
                    <path d="M28 44h8" stroke="#1e1e1e" stroke-width="3" stroke-linecap="round" />
                    <rect x="28" y="10" width="8" height="10" fill="url(#leonGradient)" />
                    <circle cx="32" cy="8" r="4" />
                </svg>
                LEON
            </div>
            <div class="leon-controls">
                <button class="leon-btn-icon" id="leonNewChatBtn" title="New Chat">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
                </button>
                <button class="leon-btn-icon" id="leonThemeBtn" title="Toggle Theme">☀️</button>
                <button class="leon-btn-icon" id="leonCloseBtn" title="Close">&times;</button>
            </div>
        </div>
        <div class="leon-chat-messages" id="leonMessages">
            <div class="leon-message ai" id="leonGreetingMsg">
                Hello! I am LEON, your AI assistant for HostelERP. How can I help you today?
            </div>
            <div class="leon-typing" id="leonTypingIndicator">
                <span></span><span></span><span></span>
            </div>
        </div>
        <div class="leon-chat-input-area">
            <input type="text" id="leonInput" class="leon-chat-input" placeholder="Type a message..." autocomplete="off" />
            <button class="leon-send-btn" id="leonSendBtn">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="white"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
            </button>
        </div>
        <div class="leon-chat-footer">
            © 2026 HostelERP All Rights Reserved.<br>
            Leon is an AI Agent and can make mistakes. Please verify every information.
        </div>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleBtn = document.getElementById('leonToggleBtn');
    const closeBtn = document.getElementById('leonCloseBtn');
    const themeBtn = document.getElementById('leonThemeBtn');
    const chatWindow = document.getElementById('leonWindow');
    const messagesContainer = document.getElementById('leonMessages');
    const inputField = document.getElementById('leonInput');
    const sendBtn = document.getElementById('leonSendBtn');
    const typingIndicator = document.getElementById('leonTypingIndicator');
    const greetingMsg = document.getElementById('leonGreetingMsg');
    const newChatBtn = document.getElementById('leonNewChatBtn');
    const userId = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;
    let isDark = true;
    themeBtn.addEventListener('click', () => {
        isDark = !isDark;
        if(isDark){
            chatWindow.classList.remove('light-theme');
            themeBtn.innerText = '☀️';
        } else {
            chatWindow.classList.add('light-theme');
            themeBtn.innerText = '🌙';
        }
    });
    function startNewChat() {
        clearMessages();
        greetingMsg.style.display = 'block';
    }
    if(newChatBtn) newChatBtn.addEventListener('click', startNewChat);
    function clearMessages() {
        const messages = messagesContainer.querySelectorAll('.leon-message');
        messages.forEach(m => m.remove());
    }
    function toggleChat() {
        if (chatWindow.style.display === 'flex') {
            chatWindow.style.display = 'none';
        } else {
            chatWindow.style.display = 'flex';
            inputField.focus();
        }
    }
    toggleBtn.addEventListener('click', toggleChat);
    closeBtn.addEventListener('click', () => {
        chatWindow.style.display = 'none';
    });
    function appendMessageRaw(sender, text, animateScroll=true) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('leon-message');
        msgDiv.classList.add(sender);
        msgDiv.innerText = text;
        messagesContainer.insertBefore(msgDiv, typingIndicator);
        if(animateScroll) messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    function appendMessage(sender, text) {
        appendMessageRaw(sender, text, true);
    }
    function showTyping() {
        typingIndicator.style.display = 'flex';
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    function hideTyping() {
        typingIndicator.style.display = 'none';
    }
    async function sendMessage() {
        const text = inputField.value.trim();
        if(!text) return;
        inputField.value = '';
        appendMessage('user', text);
        showTyping();
        try {
            const response = await fetch("http://127.0.0.1:5000/chat", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    message: text,
                    user_id: userId
                })
            });
            const data = await response.json();
            hideTyping();
            greetingMsg.style.display = 'none';
            if(data.reply) {
                appendMessage('ai', data.reply);
            } else {
                appendMessage('ai', "I didn't receive a proper response.");
            }
        } catch (error) {
            hideTyping();
            appendMessage('ai', "Error connecting to the backend server. Is it running?");
            console.error(error);
        }
    }
    sendBtn.addEventListener('click', sendMessage);
    inputField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') sendMessage();
    });
});
</script>