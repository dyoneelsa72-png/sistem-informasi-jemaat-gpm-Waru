<?php 
// Cek apakah halaman saat ini adalah halaman login
$isLoginPage = basename($_SERVER['PHP_SELF']) === 'login.php' || (isset($_GET['page']) && $_GET['page'] === 'login');
?>

<?php if ($isAdminDashboard): ?>
    </section>
    <footer class="gpm-admin-footer">
      <div><strong><i class="fa-solid fa-church"></i></strong> <em>“Melayani dengan Kasih”</em></div>
    </footer>
  </main>
</div>
<script src="ASSETS/js/gpm-menu.js"></script>
<script>
  (function () {
    var dateNode = document.getElementById('gpmToday');
    if (dateNode) {
      dateNode.textContent = new Intl.DateTimeFormat('id-ID', {
        weekday: 'long', day: '2-digit', month: 'long', year: 'numeric'
      }).format(new Date());
    }
    var toggle = document.getElementById('gpmMenuToggle');
    var sidebar = document.getElementById('gpmSidebar');
    if (toggle && sidebar) {
      toggle.addEventListener('click', function () {
        sidebar.classList.toggle('is-open');
      });
    }
  })();
</script>
<?php else: ?>
    </section>
</main>

    <!-- Footer Publik Utama (Sesuai Desain Gambar Kedua) -->
    <footer class="site-footer" style="background-color: #0f172a; color: #94a3b8; padding: 40px 20px 20px; font-family: 'Inter', sans-serif; border-top: 1px solid #1e293b;">
        <div style="max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 30px;">
            
            <!-- Kolom 1: Info Jemaat -->
            <div>
                <h4 style="color: #fbbf24; font-size: 16px; margin-bottom: 15px; font-weight: 600;">
                    <i class="fa-solid fa-church"></i> Jemaat GPM Waru
                </h4>
                <p style="font-size: 14px; line-height: 1.6; margin-bottom: 8px;">
                    <i class="fa-solid fa-location-dot" style="color: #fbbf24; margin-right: 6px;"></i> Alamat: Desa Waru, Maluku
                </p>
                <p style="font-size: 14px; line-height: 1.6;">
                    <i class="fa-solid fa-envelope" style="color: #fbbf24; margin-right: 6px;"></i> Email: info@gpmwaru.org
                </p>
            </div>

            <!-- Kolom 2: Sosial Media -->
            <div>
                <h4 style="color: #fbbf24; font-size: 16px; margin-bottom: 15px; font-weight: 600;">
                    <i class="fa-solid fa-share-nodes"></i> Sosial Media
                </h4>
                <p style="font-size: 14px; margin-bottom: 10px;">Ikuti informasi terbaru kami di:</p>
                <a href="#" style="color: #cbd5e1; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; transition: color 0.2s;">
                    <i class="fa-brands fa-facebook" style="color: #3b5998; font-size: 18px;"></i> Facebook Jemaat GPM Waru
                </a>
            </div>

            <!-- Kolom 3: Informasi Fitur -->
            <div>
                <h4 style="color: #fbbf24; font-size: 16px; margin-bottom: 15px; font-weight: 600;">
                    <i class="fa-solid fa-circle-info"></i> Informasi Fitur
                </h4>
                <p style="font-size: 14px; line-height: 1.6;">
                    Sistem Informasi Jemaat GPM Waru dikembangkan untuk mempermudah pelayanan digital warga jemaat.
                </p>
            </div>

        </div>

        <!-- Copyright / Hak Cipta -->
        <div style="max-width: 1200px; margin: 0 auto; text-align: center; border-top: 1px solid #1e293b; padding-top: 20px; font-size: 13px; color: #64748b;">
            &copy; 2026 Jemaat GPM Waru. All Rights Reserved.
        </div>
    </footer>

    <!-- JS Scripts untuk halaman publik -->
    <script src="ASSETS/js/main.js"></script>

<?php endif; ?>

</body>
</html>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var btn = document.getElementById("mobileToggle");
        var menu = document.getElementById("navMenu");

        if (btn && menu) {
            btn.onclick = function(e) {
                e.preventDefault();
                // Memunculkan atau menyembunyikan kelas 'active'
                menu.classList.toggle("active");
                console.log("Tombol hamburger berhasil ditekan!");
            };
        } else {
            console.error("Elemen mobileToggle atau navMenu tidak ditemukan di HTML!");
        }
    });
</script>