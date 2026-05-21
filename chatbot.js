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

    // Data FAQ + Keywords (untuk menangani sinonim dan kata bahasa sehari-hari)
    const FAQ_DATA = [
        {
            topic: "Cara meminjam barang",
            question: "Bagaimana prosedur atau cara meminjam barang inventaris?",
            keywords: "gimana minjem pinjem pinjam ambil barang alat",
            answer: "Untuk meminjam barang, Anda harus login terlebih dahulu melalui menu Sistem Peminjaman, pilih barang yang tersedia di katalog, lalu isi formulir peminjaman dengan tanggal ambil dan tanggal kembali."
        },
        {
            topic: "Syarat peminjaman",
            question: "Apa saja persyaratan atau syarat untuk meminjam?",
            keywords: "butuh apa aja syarat ktm ktp mahasiswa anggota",
            answer: "Syarat utama adalah Anda harus terdaftar sebagai Anggota BEM atau Mahasiswa aktif Politeknik Purbaya, serta tidak memiliki tanggungan peminjaman barang yang belum dikembalikan."
        },
        {
            topic: "Durasi peminjaman",
            question: "Berapa lama batas waktu maksimal durasi peminjaman barang?",
            keywords: "berapa hari lama waktu batas maks pinjem",
            answer: "Batas maksimal peminjaman adalah 3 hari kerja. Jika membutuhkan waktu lebih lama, silakan hubungi admin untuk melakukan perpanjangan."
        },
        {
            topic: "Denda keterlambatan",
            question: "Apakah ada denda sanksi jika terlambat mengembalikan barang?",
            keywords: "telat denda sanksi bayar hukum lewat batas",
            answer: "Ya, keterlambatan pengembalian akan dikenakan sanksi berupa denda administrasi atau pengurangan hak pinjam pada periode berikutnya. Harap kembalikan tepat waktu."
        },
        {
            topic: "Barang yang tersedia",
            question: "Barang alat inventaris apa saja yang bisa dipinjam tersedia?",
            keywords: "ada barang apa aja daftar list laptop proyektor kamera",
            answer: "Kami menyediakan berbagai inventaris seperti Laptop, Proyektor, Kamera, Sound System (Speaker), Tenda, Kursi, Meja Lipat, dan perlengkapan acara lainnya. Anda bisa melihat daftar lengkapnya di Katalog."
        },
        {
            topic: "Cara registrasi akun",
            question: "Bagaimana cara mendaftar registrasi buat akun baru?",
            keywords: "bikin akun daftar register pendaftaran belum punya akun",
            answer: "Silakan klik menu Sistem Peminjaman, lalu pilih opsi 'Belum punya akun? Daftar di sini'. Isi data diri lengkap menggunakan NIM yang valid."
        },
        {
            topic: "Status peminjaman",
            question: "Bagaimana cara cek status peminjaman riwayat saya?",
            keywords: "cek liat status riwayat disetujui ditolak progres",
            answer: "Status peminjaman (Menunggu, Disetujui, Ditolak, atau Selesai) dapat dilihat di dashboard akun Anda setelah login, pada menu 'Riwayat Peminjaman'."
        },
        {
            topic: "Pengembalian barang",
            question: "Bagaimana prosedur cara proses pengembalian barang kembali?",
            keywords: "balik balikin kembalikan lapor",
            answer: "Bawa barang yang dipinjam ke Sekretariat BEM. Admin akan mengecek kondisi barang. Jika kondisi baik sesuai saat dipinjam, admin akan mengubah status menjadi Selesai."
        },
        {
            topic: "Jam operasional",
            question: "Kapan jam operasional waktu buka tutup layanan peminjaman sekretariat?",
            keywords: "buka jam berapa tutup libur jadwal hari operasional",
            answer: "Layanan peminjaman dan pengembalian dilayani pada hari kerja (Senin - Jumat) pukul 09:00 hingga 16:00 WIB di Ruang Sekretariat BEM."
        },
        {
            topic: "Kontak admin",
            question: "Bagaimana cara menghubungi kontak hubungi nomor admin pengurus?",
            keywords: "nomor wa whatsapp email telepon call center tanya",
            answer: "Anda dapat menghubungi kami melalui email bem@purbaya.ac.id atau via WhatsApp di nomor +62 812 3456 7890. Anda juga bisa datang langsung ke Sekretariat BEM."
        },
        {
            topic: "Barang rusak/hilang",
            question: "Apa yang terjadi sanksi ganti rugi jika barang rusak atau hilang?",
            keywords: "rusak cacat hancur hilang ilang ganti rugi tanggung jawab",
            answer: "Peminjam bertanggung jawab penuh atas barang. Jika rusak atau hilang, peminjam wajib memperbaiki atau mengganti barang dengan spesifikasi yang sama (atau setara dalam bentuk uang)."
        },
        {
            topic: "Perpanjangan",
            question: "Bisakah saya memperpanjang perpanjang masa waktu peminjaman?",
            keywords: "tambah waktu perpanjang lanjut ekstra hari",
            answer: "Bisa, asalkan barang tersebut tidak sedang diantre oleh peminjam lain. Silakan ajukan perpanjangan maksimal 1 hari sebelum batas waktu habis melalui admin."
        },
        {
            topic: "Batas jumlah pinjaman",
            question: "Berapa banyak batas maksimal jumlah kuota barang yang bisa dipinjam sekaligus?",
            keywords: "maksimal berapa barang banyak jumlah kuota limit",
            answer: "Setiap pengguna dapat meminjam maksimal 3 jenis barang dalam satu kali transaksi, kecuali untuk keperluan acara besar yang sudah mendapat izin khusus dari Kepala BEM."
        },
        {
            topic: "Login gagal",
            question: "Kenapa saya tidak bisa login masuk gagal lupa password?",
            keywords: "gagal login nggak bisa masuk lupa sandi password salah",
            answer: "Pastikan username dan password Anda sudah benar. Jika lupa password, saat ini Anda harus menghubungi Admin via WhatsApp untuk melakukan reset password akun Anda."
        },
        {
            topic: "Apa itu BEM",
            question: "Apa itu definisi pengertian BEM Politeknik Purbaya organisasi?",
            keywords: "kepanjangan bem organisasi kampus purbaya",
            answer: "BEM (Badan Eksekutif Mahasiswa) Politeknik Purbaya adalah wadah aspirasi dan kolaborasi untuk mewujudkan mahasiswa yang progresif, inovatif, dan berintegritas tinggi."
        }
    ];

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
