document.addEventListener('DOMContentLoaded', function () {
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');

    if (mobileToggle && navMenu) {
        // 1. Fungsi saat tombol hamburger diklik
        mobileToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            const opened = navMenu.classList.toggle('active');
            
            // Mengubah ikon hamburger (☰) menjadi tanda silang (✕) saat dibuka
            const icon = mobileToggle.querySelector('i');
            if (icon) {
                icon.className = opened ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
            }
        });

        // 2. Menutup menu secara otomatis jika mengklik area di luar menu/tombol
        document.addEventListener('click', function (event) {
            if (!navMenu.contains(event.target) && !mobileToggle.contains(event.target)) {
                navMenu.classList.remove('active');
                
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-bars';
                }
            }
        });

        // 3. Menutup menu otomatis jika layar diubah kembali ke mode desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth > 992) {
                navMenu.classList.remove('active');
                const icon = mobileToggle.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-bars';
                }
            }
        });
    }
});