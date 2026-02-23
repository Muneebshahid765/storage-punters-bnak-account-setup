<script>
    var SITE_URL = "<?php echo site_url(); ?>"; // Define base site URL for JavaScript
    var base_url = "<?php echo base_url(); ?>"; // Unified base URL for AJAX
    window.APP_CONFIG = window.APP_CONFIG || {};
    window.APP_CONFIG.STRIPE_PUBLISHABLE_KEY = "<?= stripe_publishable_key(); ?>";
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- Bootstrap Switch JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-switch/3.3.4/js/bootstrap-switch.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= base_url('assets/js/all.js') ?>"></script>
<script src="<?= base_url('assets/js/countdown.js') ?>"></script>

<?php if (!isset($_SESSION['auction_user_session_data'])) { ?>
    <script src="<?= base_url('assets/js/ajax/authentication/login.js') ?>"></script>
    <script src="<?= base_url('assets/js/ajax/authentication/forgot_password.js') ?>"></script>
    <script src="<?= base_url('assets/js/ajax/auth/unified_auth.js') ?>"></script>
    <script src="https://connect-js.stripe.com/v1.0/connect.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
<?php } ?>



<?php
if (isset($_SESSION['auction_user_session_data'])) {
    $userSessionData = $this->session->userdata('auction_user_session_data');
    $userRoleId = $userSessionData['role_id'] ?? null;

    if ($userRoleId == 2): ?>
        <script src="<?= base_url('assets/js/ajax/dashboard/bidder/script.js') ?>"></script>
        <script src="<?= base_url('assets/js/ajax/dashboard/bidder/wallet_balance.js') ?>"></script>




    <?php elseif ($userRoleId == 1 || $userRoleId == 3): ?>
        <script src="<?= base_url('assets/js/ajax/dashboard/storage_facility/script.js') ?>"></script>
        <script src="<?= base_url('assets/js/ajax/dashboard/storage_facility/mobile_sidebar.js') ?>"></script>

    <?php elseif ($userRoleId == 4): ?>
        <script src="<?= base_url('assets/js/ajax/dashboard/admin/script.js') ?>"></script>
<?php endif;
}
?>



<?php if (isset($_SESSION['auction_user_session_data'])) { ?>

    <script src="<?= base_url('assets/js/ajax/dashboard/get_user_login.js') ?>"></script>
    <script src="<?= base_url('assets/js/ajax/authentication/logout.js') ?>"></script>
    <script src="https://js.stripe.com/v3/"></script>

<?php } ?>

<!-- Moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<!-- Moment Timezone -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.43/moment-timezone-with-data.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/luxon@3/build/global/luxon.min.js"></script>


<?php if (!empty($scripts)): ?>
    <?php foreach ($scripts as $script): ?>
        <script src="<?= $script ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<script src="<?= base_url('assets/js/dateformat.js') ?>"></script>
<script src="<?= base_url('assets/js/subscription_countdown.js') ?>"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAVfGb-g1V3gP1rHrvDB7N7HBv9Cmz9sQU&libraries=places"></script>
</body>

</html>