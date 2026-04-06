
<!-- Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-cog me-2"></i>Assistant Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">System Persona (Instructions)</label>
          <textarea id="systemPrompt" class="form-control" rows="3" placeholder="You are a helpful assistant...">You are Assist by ChippyTime. Answer concisely and use Markdown.</textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Temperature (Creativity)</label>
          <input type="range" class="form-range" id="tempRange" min="0" max="1" step="0.1" value="0.7">
          <div class="d-flex justify-content-between text-muted small">
            <span>Precise</span>
            <span id="tempValue">0.7</span>
            <span>Creative</span>
          </div>
        </div>
        <!-- Existing TTS Toggle -->
        <div class="form-check form-switch mb-3">
          <input class="form-check-input" type="checkbox" id="ttsToggle">
          <label class="form-check-label" for="ttsToggle">Read responses aloud (TTS)</label>
        </div>
<!-- Inside modal-body -->
<div class="border-top pt-3 mt-3">
  <label class="form-label text-danger small fw-bold">Data Management</label>
  <div class="d-flex gap-2">
      <a href="/download_data.php?format=json">
    <button class="btn btn-outline-primary btn-sm w-50">
      <i class="fas fa-download me-1"></i> Export JSON
    </button></a>
          <a href="/download_data.php?format=csv">
    <button class="btn btn-outline-primary btn-sm w-50">
      <i class="fas fa-download me-1"></i> Export CSV
    </button></a>
    <button id="clearChat" class="btn btn-outline-danger btn-sm w-50">
      <i class="fas fa-trash-alt me-1"></i> Clear History
    </button>
  </div>
</div>
          </button>
        <!-- NEW: Legacy Toggle -->
       <!--<div class="form-check form-switch">
          <input class="form-check-input" type="checkbox" id="legacyToggle">
          <label class="form-check-label" for="legacyToggle">Show Legacy Models</label> 
        </div>-->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-success" onclick="saveSettings()">Save</button>
      </div>
    </div>
  </div>
</div>

<nav class="navbar assist-navbar py-2 sticky-top">
  <div class="container-fluid px-4 md-px-5 d-flex align-items-center justify-content-between">
    <span class="navbar-brand fw-bold text-success d-flex align-items-center gap-2">
       <img src="/assist_logo.png" height="30px" alt="Assist logo"> <span style="color: var(--accent);">Assist</span>  <small class="text-muted fw-normal fs-6">by ChippyTime</small>
    </span>
    <div class="d-flex gap-2">
        <small class="text-muted fw-normal fs-6"><?php echo $_SESSION['username'] ?></small>
        <a href="/stats">
      <button class="btn btn-outline-secondary btn-sm rounded-circle">
<i class="fa-solid fa-chart-simple"></i>
</button></a>
        <a href="/search">
      <button class="btn btn-outline-secondary btn-sm rounded-circle">
<i class="fa-solid fa-magnifying-glass"></i>
</button></a>
        <a href="/account_settings">
      <button class="btn btn-outline-secondary btn-sm rounded-circle">
<i class="fa-solid fa-user"></i>
</button></a>
        <a href="/logout">
      <button class="btn btn-outline-secondary btn-sm rounded-circle">
    <i class="fas fa-right-from-bracket"></i>
      </button></a>
      <button class="btn btn-outline-secondary btn-sm rounded-circle" data-bs-toggle="modal" data-bs-target="#settingsModal" title="Settings">
        <i class="fas fa-cog"></i>
      </button>
      <button id="toggle-dark" class="btn btn-outline-success btn-sm rounded-circle" title="Toggle Theme">
        <i class="fas fa-moon"></i>
      </button>
    </div>
  </div>
</nav>
<main class="container-fluid px-md-5 my-3">
  <div class="row gx-4 gy-4 justify-content-center">
    <!-- Sidebar -->
    <aside class="col-lg-3">
      <div class="assist-sidebar d-flex flex-column h-100">
        <h6 class="text-uppercase text-muted fw-bold mb-3 small">Configuration</h6>
        
<label class="small fw-semibold mb-1">AI Model</label>
<select id="model" class="form-select form-select-sm mb-3 shadow-none">
    <option value="gpt-3.5-turbo">GPT-3.5</option>

<option value="gpt-4o" selected>GPT-4o</option>

</select>

        <div class="d-grid gap-2">


        </div>

        <div class="mt-auto pt-4 text-center">
             <small class="text-muted" style="font-size:0.75rem">Brought to you by <a href="https://chippytime.com">ChippyTime</a></small>
        </div>
      </div>
    </aside>

    <!-- Chat Section -->
    <section class="col-lg-9 col-xl-8">
      <div class="assist-main-card">
        <div id="chat">
           <!-- Messages injected here -->
           <div class="text-center mt-5 text-muted opacity-50">
             <i class="fas fa-comments fa-3x mb-3"></i>
             <p>Start a conversation...</p>
           </div>
        </div>
        
        <!-- Typing Indicator Container -->
        <div id="typing-indicator-container" class="ps-4 ms-2 mb-2 d-none">
          <div class="typing-indicator">
            <div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>
          </div>
        </div>

        <div class="assist-footer">
            <div class="d-flex gap-1">
                <button type="button" class="action-btn" id="upload-btn" title="Upload Image (Coming Soon)">
                    <i class="fas fa-paperclip"></i>
                </button>
                <button type="button" class="action-btn" id="mic-btn" title="Voice Input">
                    <i class="fas fa-microphone"></i>
                </button>
            </div>
            
            <textarea id="input" class="form-control" rows="1" placeholder="Type a message or /image..." autocomplete="off"></textarea>
            
            <button id="send" title="Send (Enter)">
                <i class="fas fa-paper-plane"></i>
            </button>
            <button id="stop" class="btn btn-danger rounded-3 ms-2 d-none" style="width:46px;height:46px">
                <i class="fas fa-stop"></i>
            </button>
        </div>
      </div>
    </section>
  </div>
</main>

<div class="toast-container"></div>
<input type="file" id="file-input" hidden accept="image/*">
<center>
    <small class="text-muted">
  AI responses may be inaccurate. Please verify important information.
</small>
</center>

<script>
// --- Configuration & Initialization ---
const els = {
    chat: document.getElementById('chat'),
    input: document.getElementById('input'),
    sendBtn: document.getElementById('send'),
    stopBtn: document.getElementById('stop'),
    modelSelect: document.getElementById('model'),
    clearBtn: document.getElementById('clearChat'),
    micBtn: document.getElementById('mic-btn'),
    uploadBtn: document.getElementById('upload-btn'),
    toggleDarkBtn: document.getElementById('toggle-dark'),
    typingContainer: document.getElementById('typing-indicator-container'),
    systemPrompt: document.getElementById('systemPrompt'),
    tempRange: document.getElementById('tempRange'),
    tempValue: document.getElementById('tempValue'),
    ttsToggle: document.getElementById('ttsToggle'),
    // legacyToggle: document.getElementById('legacyToggle'),
    fileInput: document.getElementById('file-input')
};

let state = {
    messages: [],
    controller: null,
    darkMode: localStorage.getItem("assist_dark") === "1",
    isSpeaking: false,
    pendingImage: null,
    settings: JSON.parse(localStorage.getItem('assist_settings')) || {
        // showLegacy: false,
        systemPrompt: "You are an AI model for the website Assist by ChippyTime, a free and netural AI assistant created by ChippyTime.com, a freedom and open software development company founded in October 2025. The URL is assist.chippytime.com. The app are powered by many large language models. Answer concisely and accurately.",
        temperature: 1,
        tts: false
    }
};

// Define which models populate when the toggle is ON
const LEGACY_MODELS = [
    { value: "gpt-3.5-turbo", text: "GPT-3.5 Turbo (Legacy)" },
    { value: "gpt-4-turbo", text: "GPT-4 Turbo (Legacy)" },
    { value: "gpt-4o-mini", text: "GPT-4o mini (Legacy)" }
];

// Markdown Setup
marked.setOptions({
    highlight: function(code, lang) {
        const language = hljs.getLanguage(lang) ? lang : 'plaintext';
        return hljs.highlight(code, { language }).value;
    },
    langPrefix: 'hljs language-'
});

// KaTeX Render Option
const renderMath = (element) => {
    if(!window.renderMathInElement) return;
    renderMathInElement(element, {
        delimiters: [
            {left: '$$', right: '$$', display: true}, // Block math
            {left: '$', right: '$', display: false},  // Inline math
            // Remove the escaped brackets as they often cause double-rendering issues
        ],
        throwOnError: false
    });
};

// --- Initialization Logic ---
(function init() {
    if(state.darkMode) document.body.classList.add('dark');
    updateThemeIcon();
    
    if(els.systemPrompt) els.systemPrompt.value = state.settings.systemPrompt;
    if(els.tempRange) els.tempRange.value = state.settings.temperature;
    if(els.tempValue) els.tempValue.textContent = state.settings.temperature;
    if(els.ttsToggle) els.ttsToggle.checked = state.settings.tts;
    // Initialize Legacy Toggle
    /*
    if(els.legacyToggle) {
        if(state.settings.showLegacy === undefined) state.settings.showLegacy = false;
        els.legacyToggle.checked = state.settings.showLegacy;
        updateLegacyModelVisibility();
    }
    */
    loadChat();
    
    if(state.messages.length > 0) {
        const welcome = els.chat.querySelector('.text-center');
        if(welcome) welcome.remove();
    }
})();

// --- Helper for Legacy Models (FIXED) ---
function updateLegacyModelVisibility() {
    /*
    const show = state.settings.showLegacy;

    LEGACY_MODELS.forEach(model => {
        // Check if this specific model is currently in the dropdown
        let option = Array.from(els.modelSelect.options).find(o => o.value === model.value);

        if (show) {
            // IF toggle ON and option missing -> Add it
            if (!option) {
                const newOpt = document.createElement("option");
                newOpt.value = model.value;
                newOpt.textContent = model.text; // Changed from .text to .textContent for safety
                newOpt.style.color = "#888"; 
                els.modelSelect.appendChild(newOpt);
            }
        } else {
            // IF toggle OFF and option exists -> Remove it
            if (option) {
                // Safety: If user has a legacy model selected, switch them to default
                if (els.modelSelect.value === model.value) {
                    els.modelSelect.value = els.modelSelect.options[0].value; 
                    showToast('Switched to default model', 'warning');
                }
                option.remove();
            }
        }
    });
    */
}

// --- Event Listeners ---
els.input.addEventListener('input', function() {
    this.style.height = '46px'; // Temporary reset to get accurate scrollHeight
    const newHeight = Math.min(this.scrollHeight, 200); // Cap height at 200px
    this.style.height = newHeight + 'px';
});
els.input.addEventListener('keydown', e => {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});

els.modelSelect.addEventListener('change', () => {
    const modelName = els.modelSelect.options[els.modelSelect.selectedIndex].text;
    showToast(`Switched to ${modelName}`, 'success');
});

els.sendBtn.onclick = sendMessage;
els.stopBtn.onclick = () => state.controller?.abort();
els.clearBtn.onclick = clearChat;
els.toggleDarkBtn.onclick = toggleTheme;
if(els.tempRange) els.tempRange.oninput = (e) => els.tempValue.textContent = e.target.value;

// --- IMAGE UPLOAD LOGIC ---
els.uploadBtn.onclick = () => els.fileInput.click();
els.fileInput.onchange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) {
        showToast('Image too large (Max 5MB)', 'warning');
        return;
    }
    const reader = new FileReader();
    reader.onload = (e) => {
        state.pendingImage = e.target.result;
        showImagePreview(state.pendingImage);
        els.input.focus();
    };
    reader.readAsDataURL(file);
};

function showImagePreview(base64) {
    let previewContainer = document.getElementById('input-preview-container');
    if (!previewContainer) {
        previewContainer = document.createElement('div');
        previewContainer.id = 'input-preview-container';
        previewContainer.className = 'px-3 py-2 d-flex align-items-center gap-2';
        previewContainer.style.background = 'rgba(230, 240, 235, 0.5)';
        previewContainer.style.borderTop = '1px solid #e0e0e0';
        const footer = document.querySelector('.assist-footer');
        footer.insertBefore(previewContainer, footer.firstChild);
    }
    previewContainer.innerHTML = `
        <div class="position-relative">
            <img src="${base64}" class="rounded border" style="height: 50px; width: auto;">
            <button onclick="clearImageAttachment()" class="btn btn-sm btn-danger position-absolute top-0 start-100 translate-middle rounded-circle p-0 d-flex align-items-center justify-content-center" style="width:18px;height:18px;font-size:10px;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <span class="text-muted small">Image attached</span>
    `;
}

window.clearImageAttachment = function() {
    state.pendingImage = null;
    els.fileInput.value = "";
    const preview = document.getElementById('input-preview-container');
    if (preview) preview.remove();
};
       async function updateSessionTitle(firstQuery) {
    // You could call a small PHP script that uses GPT-4o-mini 
    // to give a 3-word title based on the first query.
    // For now, let's just update the UI title:
    const title = firstQuery.substring(0, 30) + "...";
    document.title = "Assist | " + title;
}
// --- Core Logic ---
async function sendMessage() {
    const text = els.input.value.trim();
    const hasImage = !!state.pendingImage;
    if (!text && !hasImage) return;

    let userContentPayload;
    let uiHTML; 

    if (hasImage) {
        // Ensure the text part is never empty for multimodal models
        const promptText = text || "What is in this image?";
        userContentPayload = [
            { type: "text", text: promptText },
            { type: "image_url", image_url: { url: state.pendingImage } }
        ];
        uiHTML = `<div class="mb-2"><img src="${state.pendingImage}" class="rounded border" style="max-height: 200px; max-width: 100%;"></div><div>${marked.parse(promptText)}</div>`;
    } else {
        userContentPayload = text;
        uiHTML = marked.parse(text); // Parse markdown even for users
    }
    
    // Add to UI
    addMessage('user', uiHTML);
    state.messages.push({ role: 'user', content: userContentPayload });
    saveChat();

    els.sendBtn.classList.add('d-none');
    els.stopBtn.classList.remove('d-none');
    els.typingContainer.classList.remove('d-none'); 
    state.controller = new AbortController();

    try {
        if(text.startsWith('/image')) {
            handleImageGeneration(text);
            return;
        }

        const bubbleContent = addMessage('assistant', '', true); 
        let fullResponse = "";

        const response = await fetch('streaming.php', {
            method: 'POST',
            signal: state.controller.signal,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                model: els.modelSelect.value,
                messages: [
                    { role: 'system', content: state.settings.systemPrompt },
                    ...state.messages 
                ],
                temperature: parseFloat(state.settings.temperature),
                stream: true,
            })
        });

        els.typingContainer.classList.add('d-none');

        
        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = ''; // Add a buffer to handle fragmented chunks

        while (true) {
            const { value, done } = await reader.read();
            if (done) break;
            
            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop(); // Keep the last (potentially incomplete) line in the buffer

            for (const line of lines) {
                const cleanLine = line.trim();
                if (cleanLine.startsWith('data:') && cleanLine !== 'data: [DONE]') {
                    try {
                        const json = JSON.parse(cleanLine.substring(5));
                        const content = json.choices[0]?.delta?.content || "";
                        fullResponse += content;
                        
                        // Use a dedicated container for the markdown to avoid re-rendering helpers
                        bubbleContent.innerHTML = marked.parse(fullResponse);
                        addCodeHeaders(bubbleContent);
                    } catch (e) {
                        console.error("Streaming error:", e);
                    }
                }
            }
            els.chat.scrollTop = els.chat.scrollHeight;
        }

        renderMath(bubbleContent);
        addCodeHeaders(bubbleContent);
        state.messages.push({ role: 'assistant', content: fullResponse });
        saveChat();

// Add inside sendMessage() if state.messages.length === 1
if(state.messages.length === 1) updateSessionTitle(text);
        if(state.settings.tts) speak(fullResponse);

    } catch (err) {
        // Inside the catch (err) block of sendMessage:
const bubbleContent = addMessage('assistant', '');
bubbleContent.innerHTML = `
    <div class="text-danger p-2 border border-danger rounded">
        <i class="fas fa-wifi-slash"></i> Connection lost.
        <button class="btn btn-sm btn-outline-danger ms-2" onclick="regenerateLast()">
            <i class="fas fa-sync"></i> Retry
        </button>
    </div>
`;
    } finally {
        els.sendBtn.classList.remove('d-none');
        els.stopBtn.classList.add('d-none');
        els.typingContainer.classList.add('d-none');
        state.controller = null;
    }
}

// --- Helper Functions ---
function addMessage(role, content, isStreaming = false) {
    const isUser = role === 'user';
    const div = document.createElement('div');
    div.className = `chat-bubble ${role} fade-in`;
    
    // Icons
    const avatar = isUser ? `<div class="avatar user-avatar"><i class="fas fa-user"></i></div>` : `<div class="avatar assistant-avatar"><i class="fas fa-robot"></i></div>`;
    let innerHTML = '';
    
    if (isStreaming) {
        innerHTML = '';
    } else {
        if (typeof content === 'string' && (content.trim().startsWith('<') || isUser)) {
             innerHTML = content;
        } else {
             innerHTML = marked.parse(content);
        }
    }

    div.innerHTML = isUser 
        ? ` <div class="bubble-content shadow-sm text-break">${innerHTML}</div> ${avatar}`
        : `${avatar} 
           <div class="bubble-content shadow-sm text-break" style="min-width:60px">
              ${innerHTML}
              <div class="bubble-tools">
                <button class="tool-btn" onclick="copyToClipboard(this)"><i class="far fa-copy"></i> Copy</button>
                <button class="tool-btn" onclick="regenerateLast()"><i class="fas fa-redo"></i> Regen</button>
                <button class="tool-btn" onclick="speakFromBubble(this)"><i class="fas fa-volume-up"></i></button>
              </div>
           </div>`;

    els.chat.appendChild(div);
    if (!isStreaming && role === 'assistant') {
        const contentEl = div.querySelector('.bubble-content');
        addCodeHeaders(contentEl);
        renderMath(contentEl);
    }
    els.chat.scrollTop = els.chat.scrollHeight;
    return div.querySelector('.bubble-content');
}

function addCodeHeaders(element) {
    element.querySelectorAll('pre code').forEach((block) => {
        const pre = block.parentElement;
        if(pre.querySelector('.code-header')) return;
        hljs.highlightElement(block);
        const language = block.classList[0]?.replace('language-', '') || 'Code';
        const header = document.createElement('div');
        header.className = 'code-header';
        header.innerHTML = `<span>${language}</span> <button class="copy-code-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>`;
        pre.insertBefore(header, block);
    });
}
function copyCode(btn) {
    const code = btn.parentElement.nextElementSibling.innerText;
    navigator.clipboard.writeText(code);
    
    btn.classList.add('text-success'); // Add a success class
    btn.innerHTML = `<i class="fas fa-check"></i> Copied`;
    
    setTimeout(() => {
        btn.classList.remove('text-success');
        btn.innerHTML = `<i class="far fa-copy"></i> Copy`;
    }, 2000);
}

// --- Utilities ---
function saveSettings() {
    state.settings = {
        systemPrompt: els.systemPrompt.value,
        temperature: els.tempRange.value,
        tts: els.ttsToggle.checked,
        // showLegacy: els.legacyToggle.checked 
    };
    localStorage.setItem('assist_settings', JSON.stringify(state.settings));
    
    // updateLegacyModelVisibility(); // Update Options immediately
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
    modal.hide();
    showToast('Settings saved!');
}

function saveChat() { localStorage.setItem('assist_messages', JSON.stringify(state.messages)); }

async function loadChat() {
    try {
        const response = await fetch('/dbquery.php?limit=100', {
            credentials: 'include'
        });

        const data = await response.json();

        if (!data.success || !data.history) {
            console.warn("No chat history found.");
            return;
        }

        // Clear current UI + state
        state.messages = [];
        els.chat.innerHTML = "";

        data.history.reverse().forEach(row => {

            // -------- USER MESSAGE --------
            let userContent = parseIfJson(row.user_query);
            let userDisplay = renderContent(userContent);

            state.messages.push({
                role: "user",
                content: userContent
            });

            addMessage("user", userDisplay);

            // -------- ASSISTANT MESSAGE --------
            let aiContent = parseIfJson(row.ai_response);
            let aiDisplay = renderContent(aiContent);

            state.messages.push({
                role: "assistant",
                content: aiContent
            });

            addMessage("assistant", aiDisplay);
        });

        // Optional cache
        localStorage.setItem('assist_messages', JSON.stringify(state.messages));

    } catch (error) {
        console.error("Failed to load chat history:", error);
    }
}


// -------- HELPER: Safely Parse JSON --------
function parseIfJson(content) {
    if (typeof content !== "string") return content;

    try {
        const parsed = JSON.parse(content);
        return parsed;
    } catch {
        return content;
    }
}


// -------- HELPER: Render Text + Images --------
function renderContent(content) {

    if (Array.isArray(content)) {
        const textPart = content.find(i => i.type === 'text');
        const imgPart = content.find(i => i.type === 'image_url');

        if (imgPart) {
            return `
                <div class="mb-2">
                    <img src="${imgPart.image_url.url}" 
                         class="rounded border" 
                         style="max-height: 200px">
                </div>
                ${textPart?.text || ''}
            `;
        }

        return textPart?.text || '';
    }

    return content; // Plain text fallback
}

async function clearChat() {
    if (!confirm("Delete conversation history?")) return;

    try {
        const response = await fetch('/clear_chat.php', {
            method: 'POST',
            credentials: 'include'
        });

        const data = await response.json();

        if (!data.success) {
            showToast(data.error || "Failed to clear chat", "warning");
            return;
        }

        state.messages = [];
        els.chat.innerHTML = `
            <div class="text-center mt-5 text-muted opacity-50">
                <i class="fas fa-comments fa-3x mb-3"></i>
                <p>New conversation started.</p>
            </div>
        `;
        // Scroll to top after clearing
        els.chat.scrollTop = 0;

        localStorage.removeItem('assist_messages');
        showToast("Chat cleared");

    } catch (err) {
        console.error(err);
        showToast("Server error", "warning");
    }
}

function toggleTheme() {
    state.darkMode = !state.darkMode;
    document.body.classList.toggle('dark', state.darkMode);
    localStorage.setItem("assist_dark", state.darkMode ? "1" : "0");
    updateThemeIcon();
}
function updateThemeIcon() {
    els.toggleDarkBtn.innerHTML = state.darkMode ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
}
function exportChat(format) {
    if(state.messages.length === 0) return showToast('No chat to export', 'warning');
    let content = "", type = "text/plain", filename = "chat.txt";
    if (format === 'json') {
        content = JSON.stringify(state.messages, null, 2);
        type = "application/json"; filename = "chat.json";
    } else {
        content = state.messages.map(m => {
            let txt = Array.isArray(m.content) ? m.content.find(i=>i.type==='text')?.text : m.content;
            return `[${m.role.toUpperCase()}]: ${txt}`;
        }).join('\n\n');
    }
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([content], {type}));
    a.download = filename;
    document.body.appendChild(a); a.click(); setTimeout(() => a.remove(), 100);
}

// --- Audio ---
if ('webkitSpeechRecognition' in window || 'SpeechRecognition' in window) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const recognition = new SpeechRecognition();
    recognition.continuous = false; recognition.lang = 'en-US';
    els.micBtn.onclick = () => {
        if(els.micBtn.classList.contains('active')) { recognition.stop(); } else { recognition.start(); els.micBtn.classList.add('active'); }
    };
    recognition.onresult = (event) => {
        const transcript = event.results[0][0].transcript;
        els.input.value += (els.input.value ? ' ' : '') + transcript;
        els.input.focus();
    };
    recognition.onend = () => els.micBtn.classList.remove('active');
} else { els.micBtn.style.display = 'none'; }

function speak(text) {
    window.speechSynthesis.cancel();
    if(typeof text !== 'string' || text.startsWith('<')) return;
    const utterance = new SpeechSynthesisUtterance(text.replace(/[*#`]/g, ''));
    window.speechSynthesis.speak(utterance);
}
function speakFromBubble(btn) {
    const bubbleText = btn.closest('.bubble-content').innerText.replace('Copy', '').replace('Regen', '');
    speak(bubbleText);
}
function copyToClipboard(btn) {
    const txt = btn.closest('.bubble-content').innerText.replace(/Copy|Regen/g,'').trim();
    navigator.clipboard.writeText(txt);
    showToast('Copied to clipboard!');
}
async function regenerateLast() {
    if(state.messages.length < 2) return;
    if(state.messages[state.messages.length-1].role === 'assistant') {
        state.messages.pop();
        els.chat.lastElementChild.remove();
        sendMessage(); 
    }
}
async function handleImageGeneration(prompt) {
    const cleanPrompt = prompt.replace('/image', '').trim();
    const bubbleContent = addMessage('assistant', `ЁЯОи Generating image for: "${cleanPrompt}"...`, true);
    try {
        const response = await fetch('image.php', { method: 'POST', body: JSON.stringify({ prompt: cleanPrompt }), headers: { 'Content-Type': 'application/json' } });
        const data = await response.json();
        if (data.error) throw new Error(data.error.message || data.error);
        
        let imgUrl = null;
        if (data.choices && data.choices[0] && data.choices[0].message && data.choices[0].message.images) {
             imgUrl = data.choices[0].message.images[0].image_url.url;
        }

        if (imgUrl) {
            bubbleContent.innerHTML = `<p>Generated for: <em>${cleanPrompt}</em></p><div class="d-flex flex-wrap gap-2"><img src="${imgUrl}" class="img-fluid rounded border shadow-sm" style="max-height: 500px; width: auto;"></div>`;
            state.messages.push({ role: 'assistant', content: `![Image generated for: ${cleanPrompt}]` }); 
        } else {
             throw new Error("No image data found in response");
        }
    } catch (err) {
        bubbleContent.innerHTML = `<div class="text-danger"><i class="fas fa-exclamation-circle"></i> ${err.message}</div>`;
    } finally {
        els.sendBtn.classList.remove('d-none'); els.stopBtn.classList.add('d-none'); els.typingContainer.classList.add('d-none'); state.controller = null;
    }
}
function showToast(msg, type='success') {
    const color = type === 'success' ? '#18b175' : '#ffc107';
    const toast = document.createElement('div');
    toast.className = 'toast align-items-center text-white border-0 show mb-2';
    toast.style.backgroundColor = color;
    toast.innerHTML = `<div class="d-flex"><div class="toast-body">${msg}</div></div>`;
    document.querySelector('.toast-container').appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>