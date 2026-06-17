document.addEventListener('DOMContentLoaded', () => {
    // 1. Sticky Navbar & Active Link Switching
    const navbar = document.querySelector('.navbar');
    const sections = document.querySelectorAll('section');
    const navLinks = document.querySelectorAll('.nav-link');

    window.addEventListener('scroll', () => {
        // Sticky Navbar
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }

        // Active Link Switching
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            if (scrollY >= (sectionTop - sectionHeight / 3)) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('active');
            }
        });
    });

    // Trigger scroll event on load to handle initial state
    window.dispatchEvent(new Event('scroll'));

    // 2. Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenuClose = document.querySelector('.mobile-menu-close');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileLinks = document.querySelectorAll('.mobile-link');

    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.add('active');
    });

    mobileMenuClose.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
    });

    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.remove('active');
        });
    });

    // 3. Scroll Reveal Animations
    const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .fade-up');

    const revealOptions = {
        threshold: 0.15,
        rootMargin: "0px 0px -50px 0px"
    };

    const revealOnScroll = new IntersectionObserver(function(entries, observer) {
        entries.forEach(entry => {
            if (!entry.isIntersecting) {
                return;
            } else {
                entry.target.classList.add('active');
                observer.unobserve(entry.target);
            }
        });
    }, revealOptions);

    revealElements.forEach(el => {
        revealOnScroll.observe(el);
    });

    // Initial fade up for hero elements
    setTimeout(() => {
        const heroElements = document.querySelectorAll('.hero .fade-up');
        heroElements.forEach(el => el.classList.add('active'));
    }, 100);

    // 4. Department Modal Logic
    const deptData = {
        'bph': {
            title: 'BPH (Badan Pengurus Harian)',
            desc: 'Badan Pengurus Harian yang mengkoordinasikan seluruh aktivitas dan administrasi BEM.',
            members: [
                { name: 'Reza Surya Sanjaya', role: 'Presiden Mahasiswa', initial: 'RS' },
                { name: 'Shinta Qurrota Aini', role: 'Wakil Presiden Mahasiswa', initial: 'SQ' },
                { name: 'Sabella Norly', role: 'Sekretaris Jenderal', initial: 'SN' },
                { name: 'Zalfaa Guava Rahma', role: 'Sekretaris 1', initial: 'ZG' },
                { name: 'Ayesha Lutfun Nisa', role: 'Sekretaris 2', initial: 'AL' },
                { name: 'Eka Bintang Nabila', role: 'Bendahara 1', initial: 'EB' },
                { name: 'Mega Ario Selviyani', role: 'Bendahara 2', initial: 'MA' }
            ]
        },
        'dalam_negeri': {
            title: 'Departemen Dalam Negeri',
            desc: 'Fokus pada kesejahteraan, aspirasi, dan harmonisasi internal mahasiswa kampus.',
            members: [
                { name: 'Siti Nur Aisyah', role: 'Kepala Departemen', initial: 'SN' },
                { name: 'Ferdinand Markus Saerang', role: 'Staff Birokrasi', initial: 'FM' },
                { name: 'Hidayat', role: 'Staff Birokrasi', initial: 'H' },
                { name: 'Fadhila Kirana Utami', role: 'Staff Kemahasiswaan', initial: 'FK' }
            ]
        },
        'luar_negeri': {
            title: 'Departemen Luar Negeri',
            desc: 'Membangun relasi dan kerja sama dengan instansi, kampus lain, serta pihak eksternal.',
            members: [
                { name: 'Dewi Aulia Rizki', role: 'Kepala Departemen', initial: 'DA' },
                { name: 'Muhammad Kholiludin Rendi', role: 'Staff Diplomasi', initial: 'MK' },
                { name: 'Mumtaz Nadiyah Rahayu', role: 'Staff Pengabdian Masyarakat', initial: 'MN' }
            ]
        },
        'kominfo': {
            title: 'Departemen Kominfo',
            desc: 'Mengelola informasi, media sosial, publikasi, dan desain visual organisasi.',
            members: [
                { name: 'Ahmad Assadulah', role: 'Kepala Departemen', initial: 'AA' },
                { name: 'Zahwa Amir', role: 'Staff Desain Grafis', initial: 'ZA' }
            ]
        },
        'adkesma': {
            title: 'Departemen Adkesma',
            desc: 'Advokasi dan Kesejahteraan Mahasiswa untuk memastikan hak-hak mahasiswa terpenuhi.',
            members: [
                { name: 'Refa Apriliani', role: 'Kepala Departemen', initial: 'RA' },
                { name: 'Nadhirotus Syaima', role: 'Staff Advokasi', initial: 'NS' },
                { name: 'Devi Aliya Kurniyanti', role: 'Staff Kewirausahaan', initial: 'DA' }
            ]
        },
        'psdm': {
            title: 'Departemen PSDM',
            desc: 'Pengembangan Sumber Daya Mahasiswa melalui pelatihan dan kaderisasi kepemimpinan.',
            members: [
                { name: 'Wahfiudin Reza Pahlevi', role: 'Kepala Departemen', initial: 'WR' },
                { name: 'Nayla Salsabila', role: 'Staff Birokrasi', initial: 'NS' },
                { name: 'Sri Zakiyah', role: 'Staff Birokrasi', initial: 'SZ' },
                { name: 'Rizka Febriani', role: 'Staff Birokrasi', initial: 'RF' }
            ]
        }
    };

    const deptCards = document.querySelectorAll('.dept-card');
    const deptModal = document.getElementById('deptModal');
    const modalClose = document.querySelector('.dept-modal-close');
    const modalOverlay = document.querySelector('.dept-modal-overlay');
    const modalTitle = document.getElementById('modalDeptTitle');
    const modalDesc = document.getElementById('modalDeptDesc');
    const modalGrid = document.getElementById('modalMemberGrid');

    if(deptCards.length && deptModal) {
        deptCards.forEach(card => {
            card.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent default link behavior if any inside the card
                const deptId = card.getAttribute('data-dept');
                const data = deptData[deptId];
                
                if(data) {
                    // Populate data
                    modalTitle.textContent = data.title;
                    modalDesc.textContent = data.desc;
                    
                    // Clear existing
                    modalGrid.innerHTML = '';
                    
                    // Add members dynamically
                    data.members.forEach(member => {
                        const memberHtml = `
                            <div class="member-card">
                                <div class="member-avatar">${member.initial}</div>
                                <h4 class="member-name">${member.name}</h4>
                                <div class="member-role">${member.role}</div>
                                <a href="javascript:void(0)" class="member-social"><i class="fa-brands fa-instagram"></i></a>
                            </div>
                        `;
                        modalGrid.insertAdjacentHTML('beforeend', memberHtml);
                    });
                    
                    // Show modal and prevent body scroll
                    document.body.style.overflow = 'hidden';
                    deptModal.classList.add('active');
                }
            });
        });

        // Close logic
        const closeModal = () => {
            deptModal.classList.remove('active');
            document.body.style.overflow = ''; // Restore body scroll
        };

        modalClose.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);
    }
});
