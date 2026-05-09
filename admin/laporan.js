(function() {
    // UI Elements
    const btnToggleSidebar = document.getElementById('btnToggleSidebar');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Toggle Sidebar
    if (btnToggleSidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        });
    }
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
        });
    }

    // View Switching
    const viewCards = document.getElementById('viewCards');
    const viewReport = document.getElementById('viewReport');
    const reportTitle = document.getElementById('reportTitle');
    const filterArea = document.getElementById('filterArea');
    const reportThead = document.getElementById('reportThead');
    const reportTbody = document.getElementById('reportTbody');
    
    let currentReportType = '';
    let reportData = [];

    window.openReport = function(type) {
        currentReportType = type;
        viewCards.style.display = 'none';
        viewReport.classList.add('active');
        
        let title = '';
        if (type === 'barang') title = 'Laporan Barang';
        if (type === 'peminjaman') title = 'Laporan Peminjaman';
        if (type === 'pengembalian') title = 'Laporan Pengembalian';
        if (type === 'anggota') title = 'Laporan Anggota';
        
        reportTitle.textContent = title;
        renderFilters(type);
        loadReportData();
    };

    window.closeReport = function() {
        viewReport.classList.remove('active');
        setTimeout(() => {
            viewCards.style.display = 'block';
            currentReportType = '';
            reportData = [];
            reportTbody.innerHTML = '';
        }, 300);
    };

    function renderFilters(type) {
        let html = '';
        
        if (type === 'barang') {
            html += `
                <div class="filter-group">
                    <label>Kategori</label>
                    <select class="filter-input" id="fltKategori">
                        <option value="semua">Semua Kategori</option>
                        <option value="Barang Habis Pakai">Barang Habis Pakai</option>
                        <option value="Barang Tidak Habis Pakai">Barang Tidak Habis Pakai</option>
                    </select>
                </div>
            `;
        } else if (type === 'peminjaman') {
            html += `
                <div class="filter-group">
                    <label>Tgl Pengajuan (Dari)</label>
                    <input type="date" class="filter-input" id="fltStart">
                </div>
                <div class="filter-group">
                    <label>Tgl Pengajuan (Sampai)</label>
                    <input type="date" class="filter-input" id="fltEnd">
                </div>
                <div class="filter-group">
                    <label>Status</label>
                    <select class="filter-input" id="fltStatus">
                        <option value="semua">Semua Status</option>
                        <option value="pending">Pending</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                    </select>
                </div>
            `;
        } else if (type === 'pengembalian') {
            html += `
                <div class="filter-group">
                    <label>Tgl Dikembalikan (Dari)</label>
                    <input type="date" class="filter-input" id="fltStart">
                </div>
                <div class="filter-group">
                    <label>Tgl Dikembalikan (Sampai)</label>
                    <input type="date" class="filter-input" id="fltEnd">
                </div>
            `;
        } else if (type === 'anggota') {
            html += `
                <div class="filter-group">
                    <label>Role</label>
                    <select class="filter-input" id="fltRole">
                        <option value="semua">Semua Role</option>
                        <option value="anggota">Anggota</option>
                        <option value="admin">Admin</option>
                        <option value="kepala">Kepala</option>
                    </select>
                </div>
            `;
        }

        html += `
            <div class="filter-group">
                <button class="btn btn-primary" onclick="loadReportData()" style="padding: 0.6rem 1.5rem">
                    <i class="fa-solid fa-filter"></i> Terapkan
                </button>
            </div>
            <div class="export-actions">
                <button class="btn" style="background:#ef4444;color:white;border:none" onclick="exportPDF()">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <button class="btn" style="background:#10b981;color:white;border:none" onclick="exportExcel()">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
            </div>
        `;
        
        filterArea.innerHTML = html;
        setupTableHeaders(type);
    }

    function setupTableHeaders(type) {
        let th = '<tr>';
        if (type === 'barang') {
            th += '<th>Kode</th><th>Nama Barang</th><th>Kategori</th><th>Total</th><th>Dipinjam</th><th>Tersedia</th><th>Lokasi</th><th>Status</th>';
        } else if (type === 'peminjaman') {
            th += '<th>ID Peminjaman</th><th>Peminjam</th><th>Ormawa</th><th>Kegiatan</th><th>Tujuan</th><th>Lokasi</th><th>Tgl Pengajuan</th><th>Jml Item</th><th>Status</th><th>Penyetuju</th><th>Alasan Tolak</th>';
        } else if (type === 'pengembalian') {
            th += '<th>ID</th><th>Peminjam</th><th>Barang</th><th>Jml</th><th>Tgl Pinjam</th><th>Batas Kembali</th><th>Tgl Dikembalikan</th><th>Telat (Hari)</th><th>Kondisi</th><th>Denda (Rp)</th><th>Penerima</th>';
        } else if (type === 'anggota') {
            th += '<th>Nama Lengkap</th><th>Role</th><th>No. HP</th><th>Ormawa</th><th>Tgl Registrasi</th><th>Total Pinjam</th><th>Total Denda</th>';
        }
        th += '</tr>';
        reportThead.innerHTML = th;
    }

    window.loadReportData = async function() {
        reportTbody.innerHTML = '<tr><td colspan="15" class="empty-state"><i class="fa-solid fa-spinner fa-spin"></i> Memuat data...</td></tr>';
        
        let url = `api_laporan.php?type=${currentReportType}`;
        
        if (currentReportType === 'barang') {
            url += `&kategori=${encodeURIComponent(document.getElementById('fltKategori').value)}`;
        } else if (currentReportType === 'peminjaman') {
            url += `&start=${document.getElementById('fltStart').value}`;
            url += `&end=${document.getElementById('fltEnd').value}`;
            url += `&status=${document.getElementById('fltStatus').value}`;
        } else if (currentReportType === 'pengembalian') {
            url += `&start=${document.getElementById('fltStart').value}`;
            url += `&end=${document.getElementById('fltEnd').value}`;
        } else if (currentReportType === 'anggota') {
            url += `&role=${document.getElementById('fltRole').value}`;
        }

        try {
            const res = await fetch(url);
            const json = await res.json();
            if (json.success) {
                reportData = json.data;
                renderTableBody();
            } else {
                reportTbody.innerHTML = `<tr><td colspan="15" class="empty-state" style="color:var(--error)">Gagal memuat: ${json.message}</td></tr>`;
            }
        } catch(e) {
            reportTbody.innerHTML = `<tr><td colspan="15" class="empty-state" style="color:var(--error)">Kesalahan jaringan</td></tr>`;
        }
    };

    function renderTableBody() {
        if (!reportData.length) {
            reportTbody.innerHTML = '<tr><td colspan="15" class="empty-state">Tidak ada data ditemukan.</td></tr>';
            return;
        }

        let html = '';
        reportData.forEach(row => {
            html += '<tr>';
            if (currentReportType === 'barang') {
                html += `<td>${row.id_barang}</td><td>${row.nama_barang}</td><td>${row.kategori}</td>
                         <td>${row.stok_total}</td><td>${row.sedang_dipinjam}</td><td>${row.stok_tersedia}</td>
                         <td>${row.lokasi || '-'}</td><td>${row.status}</td>`;
            } else if (currentReportType === 'peminjaman') {
                html += `<td>${row.id_peminjaman}</td><td>${row.peminjam}</td><td>${row.asal_ormawa || '-'}</td>
                         <td>${row.nama_kegiatan}</td><td>${row.tujuan}</td><td>${row.lokasi}</td>
                         <td>${row.tgl_pengajuan}</td><td>${row.jumlah_item}</td>
                         <td><span style="text-transform:capitalize">${row.status_approval}</span></td>
                         <td>${row.admin_penyetuju || '-'}</td><td>${row.alasan_tolak || '-'}</td>`;
            } else if (currentReportType === 'pengembalian') {
                const telat = row.keterlambatan > 0 ? `<span style="color:var(--error)">${row.keterlambatan}</span>` : '0';
                html += `<td>${row.id_pengembalian}</td><td>${row.peminjam}</td><td>${row.nama_barang}</td>
                         <td>${row.jumlah}</td><td>${row.tgl_pinjam}</td><td>${row.tgl_kembali_rencana}</td>
                         <td>${row.tgl_kembali_asli}</td><td>${telat}</td>
                         <td>${row.kondisi_barang || '-'}</td><td>${parseFloat(row.denda).toLocaleString('id-ID')}</td>
                         <td>${row.admin_penerima || '-'}</td>`;
            } else if (currentReportType === 'anggota') {
                html += `<td>${row.nama_lengkap}</td><td style="text-transform:capitalize">${row.role}</td>
                         <td>${row.no_hp || '-'}</td><td>${row.asal_ormawa || '-'}</td>
                         <td>${row.created_at}</td><td>${row.total_peminjaman}</td>
                         <td>Rp ${parseFloat(row.total_denda).toLocaleString('id-ID')}</td>`;
            }
            html += '</tr>';
        });
        reportTbody.innerHTML = html;
    }

    // Export Helpers
    window.exportExcel = function() {
        if (!reportData.length) return alert('Tidak ada data untuk diekspor');
        const ws = XLSX.utils.json_to_sheet(reportData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Laporan");
        XLSX.writeFile(wb, `Laporan_${currentReportType}_${new Date().toISOString().slice(0,10)}.xlsx`);
    };

    window.exportPDF = function() {
        if (!reportData.length) return alert('Tidak ada data untuk diekspor');
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF(currentReportType === 'peminjaman' || currentReportType === 'pengembalian' ? 'l' : 'p');
        
        // Kop Surat
        doc.setFontSize(16);
        doc.setFont("helvetica", "bold");
        doc.text("BEM POLITEKNIK PURBAYA", doc.internal.pageSize.getWidth()/2, 15, { align: 'center' });
        doc.setFontSize(11);
        doc.setFont("helvetica", "normal");
        doc.text("Sistem Informasi Manajemen Inventaris & Peminjaman", doc.internal.pageSize.getWidth()/2, 22, { align: 'center' });
        doc.setFontSize(9);
        doc.text("Jl. Pancakarya No. 1, Talang, Tegal, Jawa Tengah", doc.internal.pageSize.getWidth()/2, 27, { align: 'center' });
        
        doc.setLineWidth(0.5);
        doc.line(14, 32, doc.internal.pageSize.getWidth()-14, 32);
        
        // Judul Laporan
        doc.setFontSize(12);
        doc.setFont("helvetica", "bold");
        let titleText = reportTitle.textContent.toUpperCase();
        doc.text(titleText, 14, 42);
        doc.setFontSize(9);
        doc.setFont("helvetica", "normal");
        doc.text(`Tanggal Cetak: ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')}`, 14, 47);

        // Header Table
        let head = [];
        let body = [];
        
        if (currentReportType === 'barang') {
            head = [['Kode', 'Nama', 'Kategori', 'Total', 'Dipinjam', 'Tersedia', 'Status']];
            body = reportData.map(r => [r.id_barang, r.nama_barang, r.kategori, r.stok_total, r.sedang_dipinjam, r.stok_tersedia, r.status]);
        } else if (currentReportType === 'peminjaman') {
            head = [['ID', 'Peminjam', 'Kegiatan', 'Tgl', 'Jml', 'Status', 'Tolak']];
            body = reportData.map(r => [r.id_peminjaman, r.peminjam, r.nama_kegiatan, r.tgl_pengajuan.slice(0,10), r.jumlah_item, r.status_approval.toUpperCase(), r.alasan_tolak || '-']);
        } else if (currentReportType === 'pengembalian') {
            head = [['Peminjam', 'Barang', 'Jml', 'Batas', 'Tgl Kembali', 'Telat', 'Kondisi', 'Denda']];
            body = reportData.map(r => [r.peminjam, r.nama_barang, r.jumlah, r.tgl_kembali_rencana, r.tgl_kembali_asli.slice(0,10), r.keterlambatan + ' hr', r.kondisi_barang || '-', r.denda]);
        } else if (currentReportType === 'anggota') {
            head = [['Nama Lengkap', 'Role', 'No. HP', 'Ormawa', 'Total Pinjam', 'Total Denda']];
            body = reportData.map(r => [r.nama_lengkap, r.role.toUpperCase(), r.no_hp || '-', r.asal_ormawa || '-', r.total_peminjaman, r.total_denda]);
        }

        doc.autoTable({
            startY: 52,
            head: head,
            body: body,
            theme: 'grid',
            headStyles: { fillColor: [15, 23, 42] },
            styles: { fontSize: 8, cellPadding: 2 }
        });

        doc.save(`Laporan_${currentReportType}_${new Date().toISOString().slice(0,10)}.pdf`);
    };

})();
