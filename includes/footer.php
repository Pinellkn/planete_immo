    <footer class="site-footer">
        <div class="footer-top">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand">
                        <div class="footer-logo">🏡 Planète<span>Immo</span></div>
                        <p>La référence de l'immobilier locatif au Bénin. Nous connectons propriétaires, agents et locataires pour des transactions sûres et transparentes.</p>
                        <div class="social-links">
                            <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-btn whatsapp"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" class="social-btn instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
                        </div>
                    </div>
                    <div class="footer-links">
                        <h4>Navigation</h4>
                        <ul>
                            <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-chevron-right"></i> Accueil</a></li>
                            <li><a href="<?= SITE_URL ?>/maisons.php"><i class="fas fa-chevron-right"></i> Toutes les annonces</a></li>
                            <li><a href="<?= SITE_URL ?>/maisons.php?type=villa"><i class="fas fa-chevron-right"></i> Villas</a></li>
                            <li><a href="<?= SITE_URL ?>/maisons.php?type=appartement"><i class="fas fa-chevron-right"></i> Appartements</a></li>
                            <li><a href="<?= SITE_URL ?>/contact.php"><i class="fas fa-chevron-right"></i> Contact</a></li>
                        </ul>
                    </div>
                    <div class="footer-links">
                        <h4>Mon Compte</h4>
                        <ul>
                            <li><a href="<?= SITE_URL ?>/register.php"><i class="fas fa-chevron-right"></i> Créer un compte</a></li>
                            <li><a href="<?= SITE_URL ?>/login.php"><i class="fas fa-chevron-right"></i> Se connecter</a></li>
                            <li><a href="<?= SITE_URL ?>/locataire/dashboard.php"><i class="fas fa-chevron-right"></i> Espace locataire</a></li>
                            <li><a href="<?= SITE_URL ?>/agent/dashboard.php"><i class="fas fa-chevron-right"></i> Espace agent</a></li>
                        </ul>
                    </div>
                    <div class="footer-contact">
                        <h4>Nous Contacter</h4>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Avenue Jean-Paul II, Cotonou, Bénin</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+229 21 30 00 00</span>
                        </div>
                        <div class="contact-item">
                            <i class="fab fa-whatsapp"></i>
                            <span>+229 97 00 00 00</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>contact@planeteimmo.bj</span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-clock"></i>
                            <span>Lun - Sam : 8h - 18h</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">
                <div class="footer-bottom-inner">
                    <p>© <?= date('Y') ?> <strong>PlanèteImmo</strong>. Tous droits réservés.</p>
                    <div class="footer-bottom-links">
                        <a href="#">Mentions légales</a>
                        <a href="#">Politique de confidentialité</a>
                        <a href="#">CGU</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <a href="#" class="back-to-top" id="backToTop"><i class="fas fa-arrow-up"></i></a>

    <script src="<?= SITE_URL ?>/js/main.js"></script>
</body>
</html>
