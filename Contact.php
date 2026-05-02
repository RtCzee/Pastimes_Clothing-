<?php

// Contact page - form for customer inquiries and messages
require __DIR__ . '/includes/bootstrap.php';

render_page_start([
    'title' => 'Contact | Pastimes',
    'page' => 'Contact',
]);
?>
<section class="section section--compact">
    <div class="container">
        <div class="page-heading center-text">
            <h1>Get in Touch</h1>
            <p>Have a question about our collection? Want to sell your clothes? We would love to hear from you.</p>
        </div>

        <div class="contact-layout">
            <div>
                <h2 class="subheading">Send Us a Message</h2>
                <form class="stack-form" data-contact-form>
                    <div class="form-grid">
                        <label>
                            <span>First Name</span>
                            <input class="field-input" type="text" name="firstName" placeholder="Jane" required>
                        </label>
                        <label>
                            <span>Last Name</span>
                            <input class="field-input" type="text" name="lastName" placeholder="Doe" required>
                        </label>
                    </div>
                    <label>
                        <span>Email</span>
                        <input class="field-input" type="email" name="email" placeholder="jane@example.com" required>
                    </label>
                    <label>
                        <span>Subject</span>
                        <select class="field-select" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="order">Order Inquiry</option>
                            <option value="product">Product Question</option>
                            <option value="sell">Selling Items</option>
                            <option value="returns">Returns &amp; Exchanges</option>
                            <option value="other">Other</option>
                        </select>
                    </label>
                    <label>
                        <span>Message</span>
                        <textarea class="field-textarea" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                    </label>
                    <button class="button button--wide-mobile" type="submit" data-contact-submit>
                        <span data-contact-label>Send Message</span>
                        <?= site_icon('send', 'icon icon--small') ?>
                    </button>
                </form>
            </div>

            <div class="contact-info">
                <h2 class="subheading">Contact Information</h2>
                <div class="info-list">
                    <article class="info-item">
                        <div class="info-item__icon"><?= site_icon('mail', 'icon icon--small') ?></div>
                        <div>
                            <h3>Email</h3>
                            <a href="mailto:hello@pastimes.co.za">hello@pastimes.co.za</a>
                        </div>
                    </article>
                    <article class="info-item">
                        <div class="info-item__icon"><?= site_icon('phone', 'icon icon--small') ?></div>
                        <div>
                            <h3>Phone</h3>
                            <a href="tel:+27214567890">+27 21 456 7890</a>
                        </div>
                    </article>
                    <article class="info-item">
                        <div class="info-item__icon"><?= site_icon('map-pin', 'icon icon--small') ?></div>
                        <div>
                            <h3>Address</h3>
                            <p>42 Kloof Street<br>Gardens, Cape Town 8001<br>South Africa</p>
                        </div>
                    </article>
                    <article class="info-item">
                        <div class="info-item__icon"><?= site_icon('clock', 'icon icon--small') ?></div>
                        <div>
                            <h3>Hours</h3>
                            <p>Monday - Friday: 9am - 6pm SAST<br>Saturday: 10am - 4pm SAST<br>Sunday: Closed</p>
                        </div>
                    </article>
                </div>

                <div class="contact-note">
                    <h3>Want to sell your clothes?</h3>
                    <p>We are always looking for quality secondhand pieces to add to our collection. Send us photos and descriptions of your items and we will get back to you within 48 hours with our offer.</p>
                    <p>Please include clear photos of each item, including any tags, labels, or details about the condition.</p>
                </div>

                <div class="response-box">
                    <p><strong>Response Time:</strong> We typically respond to all inquiries within 24-48 hours during business days.</p>
                </div>
            </div>
        </div>
    </div>
</section>
<?php render_page_end(); ?>
