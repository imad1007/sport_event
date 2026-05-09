
</main><!-- /.main-content -->

<!-- ===== FOOTER ===== -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand">
            <span class="logo-icon">&#9917;</span> Sport<span class="logo-accent">Events</span>
            <p>Your one-stop platform for discovering and booking sports events.</p>
        </div>
        <div class="footer-links">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="/sport_event/pages/home.php">Home</a></li>
                <li><a href="/sport_event/pages/events.php">All Events</a></li>
                <li><a href="/sport_event/pages/login.php">Login</a></li>
                <li><a href="/sport_event/pages/register.php">Register</a></li>
            </ul>
        </div>
        <div class="footer-sports">
            <h4>Sports</h4>
            <ul>
                <li>&#9917; Football</li>
                <li>&#127936; Basketball</li>
                <li>&#127934; Tennis</li>
                <li>&#127939; Running</li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; <?= date('Y') ?> SportEvents Platform. Built with PHP &amp; MySQL.</p>
    </div>
</footer>

<script>
// Mobile nav toggle
document.getElementById('navToggle').addEventListener('click', function () {
    document.getElementById('navLinks').classList.toggle('open');
});

// Auto-dismiss flash messages after 4 seconds
document.querySelectorAll('.flash').forEach(function (el) {
    setTimeout(function () { el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 400); }, 4000);
});
</script>
</body>
</html>
