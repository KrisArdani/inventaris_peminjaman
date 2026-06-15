/**
 * Database FAQ & Profil BEM Politeknik Purbaya
 * Digunakan oleh chatbot.js (Offline Client-Side AI)
 */
const FAQ_DATA = [
    {
        topic: "Cara meminjam barang",
        question: "Bagaimana prosedur atau cara meminjam barang inventaris?",
        keywords: "gimana minjem pinjem pinjam ambil barang alat prosedur mekanisme alur",
        answer: "Untuk meminjam barang, Anda harus login terlebih dahulu melalui menu Sistem Peminjaman, pilih barang yang tersedia di katalog, lalu isi formulir peminjaman dengan tanggal ambil dan tanggal kembali."
    },
    {
        topic: "Syarat peminjaman",
        question: "Apa saja persyaratan atau syarat untuk meminjam?",
        keywords: "butuh apa aja syarat ktm ktp mahasiswa anggota dokumen wajib bawa",
        answer: "Syarat utama adalah Anda harus terdaftar sebagai Anggota BEM atau Mahasiswa aktif Politeknik Purbaya, serta tidak memiliki tanggungan peminjaman barang yang belum dikembalikan."
    },
    {
        topic: "Durasi peminjaman",
        question: "Berapa lama batas waktu maksimal durasi peminjaman barang?",
        keywords: "berapa hari lama waktu batas maks pinjem durasi peminjaman",
        answer: "Batas maksimal peminjaman adalah 3 hari kerja. Jika membutuhkan waktu lebih lama, silakan hubungi admin untuk melakukan perpanjangan."
    },
    {
        topic: "Denda keterlambatan",
        question: "Apakah ada denda sanksi jika terlambat mengembalikan barang?",
        keywords: "telat denda sanksi bayar hukum lewat batas hukuman denda",
        answer: "Ya, keterlambatan pengembalian akan dikenakan sanksi berupa denda administrasi atau pengurangan hak pinjam pada periode berikutnya. Harap kembalikan tepat waktu."
    },
    {
        topic: "Barang yang tersedia",
        question: "Barang alat inventaris apa saja yang bisa dipinjam tersedia?",
        keywords: "ada barang apa aja daftar list laptop proyektor kamera speaker sound tenda kursi meja",
        answer: "Kami menyediakan berbagai inventaris seperti Laptop, Proyektor, Kamera, Sound System (Speaker), Tenda, Kursi, Meja Lipat, dan perlengkapan acara lainnya. Anda bisa melihat daftar lengkapnya di Katalog."
    },
    {
        topic: "Cara registrasi akun",
        question: "Bagaimana cara mendaftar registrasi buat akun baru?",
        keywords: "bikin akun daftar register pendaftaran belum punya akun cara gabung registrasi",
        answer: "Silakan klik menu Sistem Peminjaman, lalu pilih opsi 'Belum punya akun? Daftar di sini'. Isi data diri lengkap menggunakan NIM yang valid."
    },
    {
        topic: "Status peminjaman",
        question: "Bagaimana cara cek status peminjaman riwayat saya?",
        keywords: "cek liat status riwayat disetujui ditolak progres disetujui pending ditolak",
        answer: "Status peminjaman (Menunggu, Disetujui, Ditolak, atau Selesai) dapat dilihat di dashboard akun Anda setelah login, pada menu 'Riwayat Peminjaman'."
    },
    {
        topic: "Pengembalian barang",
        question: "Bagaimana prosedur cara proses pengembalian barang kembali?",
        keywords: "balik balikin kembalikan lapor proses pemulangan serah terima",
        answer: "Bawa barang yang dipinjam ke Sekretariat BEM. Admin akan mengecek kondisi barang. Jika kondisi baik sesuai saat dipinjam, admin akan mengubah status menjadi Selesai."
    },
    {
        topic: "Jam operasional",
        question: "Kapan jam operasional waktu buka tutup layanan peminjaman sekretariat?",
        keywords: "buka jam berapa tutup libur jadwal hari operasional jam kerja sekre",
        answer: "Layanan peminjaman dan pengembalian dilayani pada hari kerja (Senin - Jumat) pukul 09:00 hingga 16:00 WIB di Ruang Sekretariat BEM."
    },
    {
        topic: "Kontak admin",
        question: "Bagaimana cara menghubungi kontak hubungi nomor admin pengurus?",
        keywords: "nomor wa whatsapp email telepon call center tanya hubungi admin contact",
        answer: "Anda dapat menghubungi kami melalui email bem@purbaya.ac.id atau via WhatsApp di nomor +62 812 3456 7890. Anda juga bisa datang langsung ke Sekretariat BEM."
    },
    {
        topic: "Barang rusak/hilang",
        question: "Apa yang terjadi sanksi ganti rugi jika barang rusak atau hilang?",
        keywords: "rusak cacat hancur hilang ilang ganti rugi tanggung jawab pecah patah",
        answer: "Peminjam bertanggung jawab penuh atas barang. Jika rusak atau hilang, peminjam wajib memperbaiki atau mengganti barang dengan spesifikasi yang sama (atau setara dalam bentuk uang)."
    },
    {
        topic: "Perpanjangan",
        question: "Bisakah saya memperpanjang perpanjang masa waktu peminjaman?",
        keywords: "tambah waktu perpanjang lanjut ekstra hari perpanjangan pinjaman",
        answer: "Bisa, asalkan barang tersebut tidak sedang diantre oleh peminjam lain. Silakan ajukan perpanjangan maksimal 1 hari sebelum batas waktu habis melalui admin."
    },
    {
        topic: "Batas jumlah pinjaman",
        question: "Berapa banyak batas maksimal jumlah kuota barang yang bisa dipinjam sekaligus?",
        keywords: "maksimal berapa barang banyak jumlah kuota limit kuantitas max",
        answer: "Setiap pengguna dapat meminjam maksimal 3 jenis barang dalam satu kali transaksi, kecuali untuk keperluan acara besar yang sudah mendapat izin khusus dari Kepala BEM."
    },
    {
        topic: "Login gagal",
        question: "Kenapa saya tidak bisa login masuk gagal lupa password?",
        keywords: "gagal login nggak bisa masuk lupa sandi password salah error login",
        answer: "Pastikan username dan password Anda sudah benar. Jika lupa password, saat ini Anda harus menghubungi Admin via WhatsApp untuk melakukan reset password akun Anda."
    },
    {
        topic: "Pembatalan peminjaman",
        question: "Bagaimana cara membatalkan peminjaman yang sudah diajukan?",
        keywords: "batal batalin cancel hapus delete tidak jadi pinjam",
        answer: "Jika status peminjaman masih 'Menunggu Persetujuan', Anda dapat membatalkannya langsung melalui dashboard akun di menu Riwayat Peminjaman. Jika sudah disetujui, silakan hubungi admin untuk pembatalan."
    },
    {
        topic: "Peminjaman diwakilkan",
        question: "Apakah pengambilan barang peminjaman bisa diwakilkan orang lain?",
        keywords: "wakil wakilkan titip orang lain temen teman mewakili ambilkan",
        answer: "Bisa diwakilkan, dengan syarat perwakilan tersebut harus membawa bukti persetujuan peminjaman (screenshot dashboard) dan Kartu Tanda Mahasiswa (KTM) asli dari peminjam utama."
    },
    {
        topic: "Peminjaman pihak luar",
        question: "Apakah organisasi luar kampus atau alumni boleh meminjam barang?",
        keywords: "luar kampus eksternal alumni ukm lain organisasi luar pinjam",
        answer: "Peminjaman untuk pihak eksternal/luar kampus diperbolehkan dengan persetujuan tertulis dan surat permohonan resmi yang ditujukan kepada Ketua BEM Politeknik Purbaya."
    },
    {
        topic: "Peminjaman mendadak",
        question: "Apakah bisa mengajukan peminjaman mendadak di hari H acara?",
        keywords: "mendadak langsung hari h hari-h mepet tanpa booking",
        answer: "Pengajuan di hari H tidak disarankan karena admin membutuhkan waktu untuk verifikasi dan penyiapan barang. Silakan ajukan minimal H-1 sebelum pemakaian barang."
    },
    {
        topic: "Apa itu BEM",
        question: "Apa itu definisi pengertian BEM Politeknik Purbaya organisasi?",
        keywords: "kepanjangan bem organisasi kampus purbaya apa itu bem",
        answer: "BEM (Badan Eksekutif Mahasiswa) Politeknik Purbaya adalah wadah aspirasi dan kolaborasi untuk mewujudkan mahasiswa yang progresif, inovatif, dan berintegritas tinggi."
    },
    {
        topic: "Visi BEM",
        question: "Apa visi dari BEM Politeknik Purbaya?",
        keywords: "visi tujuan arah pandang bem purbaya cita cita mimpi",
        answer: "Visi BEM Politeknik Purbaya adalah: 'Mewujudkan BEM Politeknik Purbaya sebagai wadah kolaborasi aktif, progresif, dan berintegritas dalam memberikan kontribusi nyata bagi mahasiswa dan masyarakat.'"
    },
    {
        topic: "Misi BEM",
        question: "Apa saja misi dari BEM Politeknik Purbaya?",
        keywords: "misi program kerja nilai nilai langkah bem purbaya",
        answer: "Misi BEM Politeknik Purbaya: 1. Membangun internal BEM yang solid dan kekeluargaan. 2. Mewadahi aspirasi mahasiswa secara aktif dan responsif. 3. Meningkatkan sinergi antar ormawa. 4. Menyelenggarakan kegiatan sosial masyarakat berdampak nyata."
    },
    {
        topic: "Struktur Organisasi BEM",
        question: "Bagaimana struktur organisasi pengurus dan kementerian/departemen di BEM?",
        keywords: "struktur organisasi pengurus inti kementerian departemen divisi bagian bem",
        answer: "BEM dipimpin oleh Presiden Mahasiswa (Presma) dan Wakil Presma, dibantu oleh Sekretaris dan Bendahara. BEM mengawasi beberapa departemen seperti: PSDM, Kominfo, Hubungan Luar (Humas), Minat Bakat, dan Sosial Masyarakat."
    },
    {
        topic: "Departemen PSDM",
        question: "Apa tugas dan program kerja dari Departemen PSDM/Internal BEM?",
        keywords: "psdm kaderisasi internal keanggotaan tugas fungsi departemen psdm",
        answer: "Departemen PSDM (Pengembangan Sumber Daya Mahasiswa) bertanggung jawab atas keakraban internal pengurus BEM, kaderisasi mahasiswa baru, Latihan Kepemimpinan Mahasiswa (LKM), serta peningkatan kualitas SDM kampus."
    },
    {
        topic: "Departemen Kominfo",
        question: "Apa tugas dan fungsi dari Departemen Kominfo BEM?",
        keywords: "kominfo komunikasi informasi media sosial ig tiktok desain pamflet publikasi",
        answer: "Departemen Kominfo bertugas mengelola publikasi media sosial resmi BEM (Instagram, Tiktok, Web), menyebarkan informasi penting kampus, serta mendesain dokumentasi dan pamflet kegiatan BEM."
    },
    {
        topic: "Departemen Humas",
        question: "Apa tugas dari Departemen Hubungan Masyarakat (Humas) / Eksternal BEM?",
        keywords: "humas hubungan masyarakat eksternal luar relasi kampus lain studi banding",
        answer: "Departemen Humas (Eksternal) bertugas membangun jaringan kerja sama dengan pihak luar kampus, menjalin relasi dengan BEM universitas lain, serta mengelola komunikasi keluar organisasi."
    },
    {
        topic: "Departemen Minat Bakat",
        question: "Apa peran dari Departemen Minat Bakat BEM?",
        keywords: "minat bakat seni olahraga maba lomba ukm futsal musik",
        answer: "Departemen Minat Bakat memfasilitasi potensi non-akademik mahasiswa di bidang seni dan olahraga, mengoordinasikan kegiatan antar-UKM, serta menyelenggarakan kompetisi seni/olahraga tahunan kampus."
    },
    {
        topic: "Departemen Sosmas",
        question: "Apa fokus dari Departemen Sosial Masyarakat (Sosmas) BEM?",
        keywords: "sosmas sosial masyarakat pengabdian bakti sosial baksos bencana desa binaan",
        answer: "Departemen Sosmas fokus pada kegiatan sosial kemasyarakatan, seperti pengabdian masyarakat di desa binaan, bakti sosial, penggalangan dana bencana alam, serta tanggap isu-isu sosial di masyarakat."
    },
    {
        topic: "Cara bergabung BEM",
        question: "Bagaimana cara mendaftar pendaftaran masuk menjadi anggota BEM?",
        keywords: "daftar masuk gabung anggota pengurus bem oprec open recruitment syarat cara jadi pengurus",
        answer: "Pendaftaran pengurus BEM biasanya dibuka melalui Open Recruitment (Oprec) di awal kepengurusan baru. Syarat utamanya adalah mahasiswa aktif, lolos berkas administrasi, dan lolos tahap wawancara."
    },
    {
        topic: "Media sosial BEM",
        question: "Apa saja media sosial resmi milik BEM Politeknik Purbaya?",
        keywords: "sosmed media sosial ig instagram tiktok youtube email bem purbaya",
        answer: "Media sosial resmi kami adalah: Instagram @bem_purbaya, Tiktok @bem.purbaya, YouTube BEM Politeknik Purbaya, dan email resmi bem@purbaya.ac.id."
    },
    {
        topic: "Jadwal Oprec BEM",
        question: "Kapan pendaftaran Open Recruitment BEM dibuka?",
        keywords: "kapan oprec daftar bem buka pendaftaran pendaftaran baru jadwal rekrutmen",
        answer: "Open Recruitment pengurus BEM biasanya dibuka setiap awal tahun ajaran baru atau sekitar bulan Oktober, setelah pelantikan Presiden dan Wakil Presiden Mahasiswa yang baru."
    },
    {
        topic: "BEM vs DPM",
        question: "Apa perbedaan antara BEM (Eksekutif) dan DPM (Legislatif) di kampus?",
        keywords: "beda bem dpm legislatif eksekutif dewan perwakilan mahasiswa",
        answer: "BEM adalah lembaga Eksekutif yang menjalankan program kerja, kegiatan, dan pelayanan mahasiswa. Sedangkan DPM (Dewan Perwakilan Mahasiswa) adalah lembaga Legislatif yang bertugas membuat undang-undang ormawa, menyerap aspirasi, dan mengawasi kinerja BEM."
    },
    {
        topic: "Aspirasi Mahasiswa",
        question: "Bagaimana cara menyalurkan saran kritik atau aspirasi ke BEM?",
        keywords: "saran kritik aduan aspirasi keluhan suara mahasiswa lapor kotaksaran",
        answer: "Aspirasi dapat disalurkan melalui kotak saran fisik di depan Sekretariat BEM, melalui Google Form aspirasi online yang ada di link bio Instagram @bem_purbaya, atau berdiskusi langsung dengan departemen terkait."
    },
    {
        topic: "Peminjaman libur semester",
        question: "Apakah bisa meminjam barang inventaris saat masa libur semester?",
        keywords: "libur semester jeda semester kuliah liburan kosong tutup kuliah pinjam",
        answer: "Peminjaman saat libur semester tetap dilayani, namun harus dilakukan melalui pengajuan langsung ke WhatsApp Admin (tidak lewat sistem web) karena sekretariat BEM tidak buka setiap hari selama liburan."
    },
    {
        topic: "Kondisi barang kotor",
        question: "Bagaimana jika barang yang dikembalikan dalam kondisi kotor?",
        keywords: "kotor berdebu cuci bersihkan berlumpur bekas pakai",
        answer: "Peminjam wajib mengembalikan barang dalam kondisi bersih dan rapi seperti saat pertama kali diambil. Jika dikembalikan kotor, admin berhak menolak pengembalian sampai barang tersebut dibersihkan terlebih dahulu."
    },
    {
        topic: "Mengubah tanggal peminjaman",
        question: "Bagaimana cara mengubah tanggal atau detail peminjaman yang sudah dikirim?",
        keywords: "ubah tanggal ganti hari jadwal ganti tanggal edit detail peminjaman salah input",
        answer: "Jika status peminjaman masih 'Menunggu', silakan batalkan peminjaman tersebut lalu buat pengajuan baru dengan tanggal yang benar. Jika sudah disetujui, Anda harus menghubungi admin via WhatsApp untuk melakukan perubahan jadwal."
    },
    {
        topic: "Barang rusak sejak awal",
        question: "Apa yang harus dilakukan jika barang yang saya terima ternyata sudah rusak/cacat sejak awal?",
        keywords: "rusak dari awal cacat awal rusak pas ambil ga bisa nyala eror pertama kali",
        answer: "Segera laporkan kepada admin yang bertugas saat serah terima barang di sekretariat. Admin akan mencatat kondisi awal barang tersebut atau menggantinya dengan unit cadangan yang masih berfungsi dengan baik."
    },
    {
        topic: "Waktu maksimal pengajuan",
        question: "Berapa hari sebelum acara saya harus mengajukan peminjaman?",
        keywords: "paling lambat maksimal minimal berapa hari sebelum acara booking jauh hari h-berapa",
        answer: "Anda disarankan mengajukan peminjaman paling lambat H-3 sebelum acara. Pengajuan jauh-jauh hari (misal H-7 atau H-14) sangat direkomendasikan untuk memastikan ketersediaan barang."
    },
    {
        topic: "Kegiatan rutin BEM",
        question: "Apa saja kegiatan rutin bulanan atau tahunan yang diselenggarakan BEM?",
        keywords: "kegiatan rutin bulanan tahunan rapat kerja proker rutin event tahunan",
        answer: "Kegiatan rutin kami meliputi Forum Komunikasi Ormawa bulanan, bakti sosial berkala, Latihan Kepemimpinan Mahasiswa (LKM), serta perayaan Dies Natalis kampus dan kompetisi olahraga tahunan."
    },
    {
        topic: "Bantuan beasiswa",
        question: "Apakah BEM menyediakan bantuan informasi atau pengajuan beasiswa?",
        keywords: "beasiswa kip-k prestasi bantuan dana kurang mampu beasiswa kuliah",
        answer: "BEM tidak menyalurkan beasiswa secara langsung, namun Departemen Advokasi BEM aktif membagikan informasi beasiswa (seperti KIP Kuliah, beasiswa prestasi, dll) dan membantu memfasilitasi mahasiswa yang mengalami kendala administrasi beasiswa ke pihak kampus."
    },
    {
        topic: "Pendanaan BEM",
        question: "Dari mana sumber pendanaan organisasi BEM Politeknik Purbaya?",
        keywords: "dana bem uang bem dana kemahasiswaan sponsor iuran kas anggaran bem",
        answer: "Sumber pendanaan BEM berasal dari Anggaran Kemahasiswaan Kampus Politeknik Purbaya, usaha dana mandiri (danus) pengurus, serta sponsorship resmi yang tidak mengikat dalam kegiatan tertentu."
    },
    {
        topic: "Kolaborasi dengan BEM",
        question: "Bagaimana cara berkolaborasi atau bekerja sama membuat acara dengan BEM?",
        keywords: "kolaborasi kerja sama bareng ukm ormawa instansi luar proposal kerjasama",
        answer: "Anda dapat mengirimkan proposal kerja sama atau surat permohonan kolaborasi resmi ke email bem@purbaya.ac.id atau datang langsung ke Sekretariat BEM untuk berdiskusi dengan Departemen Hubungan Luar (Humas)."
    },
    {
        topic: "Layanan advokasi",
        question: "Apa itu layanan advokasi mahasiswa yang disediakan BEM?",
        keywords: "advokasi aduan masalah ukt fasilitas kampus dosen nilai bermasalah curhat",
        answer: "Layanan advokasi BEM membantu mahasiswa yang mengalami kendala akademik, fasilitas kelas yang rusak, masalah pembayaran UKT, atau kendala birokrasi kampus. Kami bertindak sebagai penyambung lidah ke pihak manajemen kampus."
    },
    {
        topic: "Jumlah anggota BEM",
        question: "Ada berapa jumlah pengurus atau anggota aktif BEM periode ini?",
        keywords: "jumlah orang anggota pengurus bem total personel staf kabinet",
        answer: "Jumlah pengurus BEM Politeknik Purbaya periode ini adalah sekitar 45-50 mahasiswa yang terbagi ke dalam pengurus harian inti dan 5 departemen utama."
    },
    {
        topic: "Nama kabinet BEM",
        question: "Apa nama kabinet BEM Politeknik Purbaya periode saat ini?",
        keywords: "nama kabinet nama bem tahun ini periode sekarang filosofi kabinet",
        answer: "Kabinet BEM periode ini bernama Kabinet Sinergi Progresif. Nama ini mencerminkan semangat kolaborasi yang harmonis serta langkah yang terus maju ke depan demi kebaikan mahasiswa Politeknik Purbaya."
    },
    {
        topic: "BEM diikuti semua jurusan",
        question: "Apakah keanggotaan BEM terbuka untuk semua jurusan di Politeknik Purbaya?",
        keywords: "jurusan apa aja boleh daftar prodi teknik komputer akuntansi teknik mesin informatika",
        answer: "Ya, keanggotaan BEM terbuka lebar untuk seluruh mahasiswa aktif dari semua Program Studi (Jurusan) di Politeknik Purbaya tanpa terkecuali."
    },
    {
        topic: "Sertifikat kepengurusan BEM",
        question: "Apakah pengurus BEM akan mendapatkan sertifikat di akhir kepengurusan?",
        keywords: "sertifikat piagam sk pengurus portofolio cv penghargaan kerja bem",
        answer: "Ya, setiap pengurus yang menyelesaikan masa baktinya dengan baik selama satu periode akan mendapatkan Sertifikat Kepengurusan resmi yang ditandatangani oleh Direktur/Wadir Kemahasiswaan dan Presiden Mahasiswa."
    },
    {
        topic: "Uang kas BEM",
        question: "Apakah anggota BEM diwajibkan membayar iuran uang kas?",
        keywords: "uang kas iuran bulanan bayar kas bendahara bem denda kas",
        answer: "Ya, untuk mendukung operasional internal dan kebersamaan pengurus, anggota BEM membayar iuran kas bulanan dengan nominal yang telah disepakati bersama dalam Rapat Kerja di awal periode."
    }
];
