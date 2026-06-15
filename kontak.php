<?php
$page_title = 'Contact Us';
require_once 'includes/header.php';
?>

<main>
    <section class="hero" style="height: 400px;">
        <img class="hero-img" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDjzpWfMY4hy-jh32Je-SCjhwmgVdtdZw4ZOZfy3bV7LaYH42F0zVPE4-rli3zTO88UdAJaNmnnzvl-Z2TeO_8perbcSlbulJVGkwq1HQUc_Fn1tqA46piU6QCl_fX8yZO4ofCGzibUvp9cAP4vFZuvo8e4w6AAv1llkbOAOrL-VHO4MmUGw7tp5rF-Ar9rE5khuGKZWKqVeUR8ooSAzrWrSKKz2ezC1CFosyoWKzaqO2Pvfx-8V-W6KfPDfWbJ2z7k1w1L4QVTu5Y" alt="Contact Hero"/>
        <div class="container" style="text-align: center;">
            <h1 style="font-size: 48px; margin-bottom: 16px;">Connect With Us</h1>
            <p style="max-width: 700px; margin: 0 auto; font-size: 18px;">From the golden hives of BatuMekar to your home. Reach out for inquiries about our harvest, sustainability practices, or wholesale opportunities.</p>
        </div>
    </section>

    <section style="padding: 80px 0;">
        <div class="container grid grid-2">
            <div class="card" style="padding: 32px;">
                <h2 style="margin-bottom: 24px; color: var(--secondary);">Send a Message</h2>
                <form style="display: flex; flex-direction: column; gap: 8px;">
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 8px;">Full Name</label>
                        <input type="text" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 8px;">Email</label>
                        <input type="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label style="display: block; margin-bottom: 8px;">Message</label>
                        <textarea class="form-control" rows="5" required style="resize: vertical;"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="align-self: flex-start; padding: 14px 32px; margin-top: 12px;">Send Message</button>
                </form>
            </div>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <div class="card" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border: 1px solid var(--outline-variant); color: var(--on-primary-container); padding: 32px;">
                    <h3 style="margin-bottom: 20px; font-size: 24px; color: var(--primary);">Direct Reach</h3>
                    <div style="display: flex; flex-direction: column; gap: 16px;">
                        <div>
                            <p style="margin-bottom: 4px; font-weight: 700; color: var(--secondary);">Email Us</p>
                            <p style="margin: 0; font-size: 16px;">harvest@batumekar.com</p>
                        </div>
                        <div>
                            <p style="margin-bottom: 4px; font-weight: 700; color: var(--secondary);">Visit Us</p>
                            <p style="margin: 0; font-size: 16px; line-height: 1.5;">BatuMekar Village, Klungkung, Bali</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>
