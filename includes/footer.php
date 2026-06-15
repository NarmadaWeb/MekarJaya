<!-- Footer -->
<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-col footer-brand">
            <a href="index.php" class="footer-logo">BatuMekar</a>
            <p style="color: #b0a79a; line-height: 1.6; margin: 0;">Bringing the golden warmth of the village to your home, one jar of pure artisanal honey at a time.</p>
            <div class="footer-socials">
                <a href="#" class="social-icon" aria-label="Facebook">
                    <span class="material-symbols-outlined">share</span>
                </a>
                <a href="#" class="social-icon" aria-label="Instagram">
                    <span class="material-symbols-outlined">photo_camera</span>
                </a>
                <a href="#" class="social-icon" aria-label="YouTube">
                    <span class="material-symbols-outlined">smart_display</span>
                </a>
            </div>
        </div>
        <div class="footer-col">
            <h4>Village</h4>
            <ul class="footer-links">
                <li><a href="tentang-desa.php"><span class="material-symbols-outlined">info</span> About Our Story</a></li>
                <li><a href="#"><span class="material-symbols-outlined">calendar_today</span> Harvest Calendar</a></li>
                <li><a href="#"><span class="material-symbols-outlined">eco</span> Sustainability</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Support</h4>
            <ul class="footer-links">
                <li><a href="bantuan.php" style="color: var(--primary-container); font-weight: 600;"><span class="material-symbols-outlined">help</span> Help Center</a></li>
                <li><a href="#"><span class="material-symbols-outlined">local_shipping</span> Shipping Policy</a></li>
                <li><a href="#"><span class="material-symbols-outlined">gavel</span> Terms of Service</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h4>Connect</h4>
            <ul class="footer-links">
                <li><a href="kontak.php"><span class="material-symbols-outlined">mail</span> Contact Us</a></li>
                <li><a href="reseller/dashboard.php"><span class="material-symbols-outlined">storefront</span> Wholesale Portal</a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <p style="margin: 0;">© 2026 BatuMekar Village. Crafted by Nature with Passion.</p>
        <p style="margin: 0; font-size: 12px; opacity: 0.6;">All rights reserved. Purity Guaranteed.</p>
    </div>
</footer>
<script>
    function toggleAccordion(element) {
        const item = element.parentElement;
        const isActive = item.classList.contains('active');

        // Close all items
        document.querySelectorAll('.accordion-item').forEach(el => {
            el.classList.remove('active');
        });

        // Toggle clicked item
        if (!isActive) {
            item.classList.add('active');
        }
    }
</script>
</body>
</html>

