/**
 * AI Chatbot FAQ (TF-IDF + Cosine Similarity)
 * Berjalan 100% di client-side
 */

function initChatbot() {
    console.log("[Chatbot] Initialization started...");

    // ==========================================
    // 1. DATA & KONFIGURASI
    // ==========================================

    const THRESHOLD = 0.08; // Nilai kemiripan minimum (diturunkan agar lebih toleran)

    // Daftar kata yang akan diabaikan (Stopwords)
    const STOPWORDS = [
        "yang", "di", "ke", "dari", "pada", "dalam", "untuk", "dengan", "dan", "atau", "ini", "itu", 
        "juga", "sudah", "saya", "kamu", "dia", "mereka", "kita", "kami", "adalah", "sebagai", 
        "apakah", "bagaimana", "mengapa", "kenapa", "kapan", "siapa", "dimana", "kemana", "apa",
        "ada", "bisa", "dapat", "boleh", "harus", "akan", "ingin", "mau", "punya", "banyak", 
        "sedikit", "saja", "lagi", "terus", "lalu", "setelah", "sebelum", "kalau", "jika", "bila",
        "agar", "supaya", "karena", "sebab", "sehingga", "maka", "namun", "tetapi", "tapi", 
        "walaupun", "meskipun", "sementara", "sedangkan", "hingga", "sampai", "sejak", "sangat", 
        "paling", "lebih", "kurang", "semua", "seluruh", "beberapa", "sebagian", "setiap", "masing", 
        "para", "sih", "dong", "deh", "kan", "kok", "yah", "ya", "tidak", "bukan", "belum", "jangan",
        "min", "kak", "bang", "mas", "mbak", "pak", "bu", "buat", "sih"
    ];

    // Pastikan FAQ_DATA dari faq_data.js sudah ter-load dengan benar
    if (typeof FAQ_DATA === 'undefined') {
        console.error("[Chatbot] Error: FAQ_DATA belum didefinisikan! Pastikan faq_data.js di-load sebelum chatbot.js di index.html.");
        return;
    }


    // ==========================================
    // 2. LOGIKA NLP (TF-IDF & Cosine Similarity)
    // ==========================================

    // A. Preprocessing
    function preprocess(text) {
        if (!text) return [];
        let cleanText = text.toLowerCase().replace(/[^\w\s]/gi, ' ');
        let tokens = cleanText.split(/\s+/).filter(word => word.length > 1);
        let filtered = tokens.filter(word => !STOPWORDS.includes(word));
        return filtered.length > 0 ? filtered : tokens;
    }

    // Siapkan corpus: gabungan dari QUESTION, KEYWORDS, dan ANSWER agar pencarian lebih pintar
    const docsTokens = FAQ_DATA.map(item => {
        const fullText = `${item.question} ${item.keywords} ${item.answer}`;
        return preprocess(fullText);
    });

    // Kumpulkan semua vocabulary unik
    const vocabulary = new Set();
    docsTokens.forEach(tokens => tokens.forEach(token => vocabulary.add(token)));
    const vocabArray = Array.from(vocabulary);

    // B. Hitung IDF
    const idfMap = {};
    const totalDocs = docsTokens.length;
    vocabArray.forEach(term => {
        let docsWithTerm = 0;
        docsTokens.forEach(tokens => {
            if (tokens.includes(term)) docsWithTerm++;
        });
        idfMap[term] = Math.log(totalDocs / (docsWithTerm || 1)) + 1; 
    });

    // C. Hitung TF-IDF Vektor
    function getTfIdfVector(tokens) {
        const vector = new Array(vocabArray.length).fill(0);
        const totalTokens = tokens.length;
        if (totalTokens === 0) return vector;

        const tfMap = {};
        tokens.forEach(term => {
            tfMap[term] = (tfMap[term] || 0) + 1;
        });

        vocabArray.forEach((term, index) => {
            if (tfMap[term]) {
                let tf = tfMap[term] / totalTokens;
                let idf = idfMap[term] || 0; 
                vector[index] = tf * idf;
            }
        });
        return vector;
    }

    const faqVectors = docsTokens.map(tokens => getTfIdfVector(tokens));

    // D. Cosine Similarity
    function cosineSimilarity(vecA, vecB) {
        let dotProduct = 0;
        let normA = 0;
        let normB = 0;
        for (let i = 0; i < vecA.length; i++) {
            dotProduct += vecA[i] * vecB[i];
            normA += vecA[i] * vecA[i];
            normB += vecB[i] * vecB[i];
        }
        if (normA === 0 || normB === 0) return 0;
        return dotProduct / (Math.sqrt(normA) * Math.sqrt(normB));
    }

    // E. Orchestrator
    function findBestAnswer(query) {
        const queryTokens = preprocess(query);
        const queryVector = getTfIdfVector(queryTokens);
        
        let bestScore = -1;
        let bestIndex = -1;

        for (let i = 0; i < faqVectors.length; i++) {
            let score = cosineSimilarity(queryVector, faqVectors[i]);
            if (score > bestScore) {
                bestScore = score;
                bestIndex = i;
            }
        }

        console.log(`[Chatbot] Query: "${query}" | Best Match: FAQ[${bestIndex}] | Score: ${bestScore.toFixed(3)}`);

        if (bestScore >= THRESHOLD) {
            return FAQ_DATA[bestIndex].answer;
        } else {
            return "Maaf, saya belum mengerti maksud pertanyaan Anda. Bisa dicoba dengan kata kunci lain? Atau Anda dapat menghubungi admin di WhatsApp +62 812 3456 7890.";
        }
    }


    // ==========================================
    // 3. UI CHATBOT INTERACTION
    // ==========================================

    const chatbotToggle = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const chatbotClose = document.getElementById('chatbot-close');
    const chatbotMessages = document.getElementById('chatbot-messages');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSend = document.getElementById('chatbot-send');
    const quickQuestions = document.querySelectorAll('.chatbot-chip');

    if (!chatbotToggle || !chatbotWindow) {
        console.error("[Chatbot] Error: Elemen HTML chatbot tidak ditemukan!");
        return;
    }

    console.log("[Chatbot] UI Elements found. Binding events...");

    let isFirstOpen = true;

    // Toggle window
    function openChat() {
        chatbotWindow.classList.add('active');
        chatbotToggle.style.transform = 'scale(0)';
        
        if(isFirstOpen) {
            setTimeout(() => {
                appendMessage("Halo! 👋 Saya Asisten BEM Purbaya. Ada yang bisa saya bantu terkait peminjaman barang atau info BEM?", 'bot');
            }, 300);
            isFirstOpen = false;
        }
        chatbotInput.focus();
    }

    function closeChat() {
        chatbotWindow.classList.remove('active');
        chatbotToggle.style.transform = 'scale(1)';
    }

    chatbotToggle.addEventListener('click', openChat);
    chatbotClose.addEventListener('click', closeChat);

    // Fungsi Render Pesan
    function appendMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `chatbot-msg ${sender}`;
        msgDiv.textContent = text;
        
        // Remove typing indicator if exists
        const typingInd = document.getElementById('chatbot-typing');
        if(typingInd) typingInd.remove();
        
        chatbotMessages.appendChild(msgDiv);
        scrollToBottom();
    }

    function showTyping() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chatbot-msg bot typing-indicator';
        typingDiv.id = 'chatbot-typing';
        typingDiv.innerHTML = '<span></span><span></span><span></span>';
        chatbotMessages.appendChild(typingDiv);
        scrollToBottom();
    }

    function scrollToBottom() {
        setTimeout(() => {
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }, 50);
    }

    // Handle pengiriman pesan
    function handleSend() {
        const text = chatbotInput.value.trim();
        if(!text) return;
        
        // 1. Tampilkan pesan user
        appendMessage(text, 'user');
        chatbotInput.value = '';
        
        // 2. Tampilkan typing indicator
        showTyping();
        
        // 3. Proses jawaban dengan sedikit delay
        setTimeout(() => {
            const answer = findBestAnswer(text);
            appendMessage(answer, 'bot');
        }, 500 + Math.random() * 300); // 500-800ms delay
    }

    chatbotSend.addEventListener('click', handleSend);
    chatbotInput.addEventListener('keypress', (e) => {
        if(e.key === 'Enter') handleSend();
    });

    // Handle Quick Questions (Chips)
    quickQuestions.forEach(chip => {
        chip.addEventListener('click', () => {
            const query = chip.textContent;
            appendMessage(query, 'user');
            showTyping();
            
            setTimeout(() => {
                const answer = findBestAnswer(query);
                appendMessage(answer, 'bot');
            }, 600);
        });
    });
}

// Inisialisasi secara aman
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initChatbot);
} else {
    initChatbot();
}
