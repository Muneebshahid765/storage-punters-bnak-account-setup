<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/storage_facility/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark d-lg-none me-3 p-0 hamburger-menu-btn">
                    <i class="bi bi-list fs-1"></i>
                </button>
                <h1 class="dash-title-modern mb-0">Facility Performance Dashboard</h1>
            <?php 
// Show phone verification modal if needed
if ($this->session->userdata('show_phone_verification')): 
    $this->load->view('auth/phone_verification_modal', [
        'prefilled_phone' => $user_phone ?? ''
    ]);
    $this->session->unset_userdata('show_phone_verification');
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var phoneModal = new bootstrap.Modal(document.getElementById('phoneVerificationModal'));
    phoneModal.show();
});
</script>
<?php endif; ?>

</div>
            <button class="btn-export-modern d-none d-sm-flex">
                <i class="bi bi-download"></i> Export to CSV
            </button>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid-modern">
            <div class="stat-card-modern">
                <div class="stat-label-modern">Total Auctions</div>
                <div class="stat-value-modern text-dark"><?php echo $dashboardStats['total_auctions'] ?? '0'; ?></div>
            </div>
            <div class="stat-card-modern">
                <div class="stat-label-modern">Total Sold</div>
                <div class="stat-value-modern text-dark"><?php echo $dashboardStats['total_sold'] ?? '0'; ?></div>
            </div>
            <div class="stat-card-modern">
                <div class="stat-label-modern">Avg. Sale Price</div>
                <div class="stat-value-modern text-dark">$<?php echo $dashboardStats['avg_sale_price'] ?? '0.00'; ?></div>
            </div>
            <div class="stat-card-modern">
                <div class="stat-label-modern">Avg. Price / m²</div>
                <div class="stat-value-modern text-dark">$<?php echo $dashboardStats['avg_price_m2'] ?? '0.00'; ?></div>
            </div>
        </div>

        <!-- Financial Recovery Section -->
        <section class="content-section-modern">
            <h2 class="section-title-modern">Financial Recovery by Location (YTD)</h2>
            <p class="section-subtitle-modern">Comparison of Total Debt placed on auction vs. Actual Revenue Recovered (Final Sale Price).</p>
            
            <div class="table-responsive">
                <table class="table-modern">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Location</th>
                            <th class="text-end">Total Debt Value ($)</th>
                            <th class="text-end">Recovered Revenue ($)</th>
                            <th class="text-end">Recovery Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($recoveryStats)): ?>
                            <?php foreach($recoveryStats as $stat): ?>
                            <tr>
                                <td data-label="Location"><?php echo $stat['location_name']; ?></td>
                                <td class="text-end" data-label="Total Debt Value">$<?php echo number_format($stat['total_debt'], 0); ?></td>
                                <td class="text-end text-revenue-green" data-label="Recovered Revenue">$<?php echo number_format($stat['recovered_revenue'], 0); ?></td>
                                <td class="text-end" data-label="Recovery Rate"><?php echo $stat['recovery_rate']; ?>%</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted small">No recovery data available for this period</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Auctions Sold Section -->
        <section class="content-section-modern">
            <h2 class="section-title-modern">Auctions Sold by Location</h2>
            <p class="section-subtitle-modern">Total number of auctions successfully closed (sold) per facility.</p>
            
            <div class="chart-placeholder-modern">
                <?php if(!empty($soldByLocation)): ?>
                    <?php 
                        $maxSold = max(array_column($soldByLocation, 'sold_count')) ?: 1;
                        foreach($soldByLocation as $loc): 
                            $height = ($loc['sold_count'] / $maxSold) * 160 + 40; // Scale between 40px and 200px
                    ?>
                    <div class="d-flex flex-column align-items-center gap-2">
                        <div style="width: 60px; height: <?php echo $height; ?>px; background-color: #E8EDE9; border-radius: 4px;"></div>
                        <span class="chart-bar-label"><?php echo $loc['location_name']; ?> (<?php echo $loc['sold_count']; ?>)</span>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center w-100 py-5 text-muted small">
                        <i class="bi bi-bar-chart d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                        No sales data found for chart
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Legacy Data Integration (Auctions List) -->
        <div class="mt-5">
            <div class="__tab__table_auction__dashboard">
                <!-- JS Data Injection Container -->
                <div class="text-center p-5 opacity-50">
                    <i class="bi bi-arrow-repeat spin display-6 mb-3 d-block"></i>
                    Synchronizing live auction data...
                </div>
            </div>
        </div>
    </main>
</div>