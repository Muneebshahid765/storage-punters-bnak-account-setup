<aside class="sidebar-modern">
    <div class="sidebar-header-modern">
        <div class="d-flex justify-content-between align-items-center w-100">
             <div>
                <div class="sidebar-brand-name">Storage HQ</div>
                <div class="sidebar-facility-name"><?php echo $userSessionData['first_name'] ?? 'Facility Name'; ?></div>
             </div>
             <!-- Mobile Close Button -->
             <button class="btn btn-sm btn-link d-lg-none text-dark close-sidebar-btn">
                <i class="bi bi-x-lg"></i>
             </button>
        </div>
    </div>
    
    <nav class="sidebar-nav-modern">
        <?php 
            $segment2 = $this->uri->segment(2); 
            $segment1 = $this->uri->segment(1);
        ?>
        <a href="<?php echo site_url('storage-facility/dashboard'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'dashboard') ? 'active' : ''; ?>">
            <i class="bi bi-grid-fill me-2"></i> Dashboard
        </a>
        <a href="<?php echo site_url('storage-facility/auctions'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'auctions') ? 'active' : ''; ?>">
            <i class="bi bi-hammer me-2"></i> My Auctions
        </a>
        <a href="<?php echo site_url('storage-facility/ai/new'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'ai') ? 'active' : ''; ?>">
            <i class="bi bi-stars me-2 text-gold"></i> AI Smart Listing
        </a>
        <a href="<?php echo site_url('company'); ?>" class="sidebar-link-modern <?php echo ($segment1 == 'company') ? 'active' : ''; ?>">
            <i class="bi bi-gear-fill me-2"></i> Settings Hub
        </a>
        <a href="<?php echo site_url('storage-facility/staff/manage'); ?>" class="sidebar-link-modern <?php echo ($segment2 == 'staff') ? 'active' : ''; ?>">
            <i class="bi bi-people-fill me-2"></i> Manage Staff
        </a>
        <a href="#" class="sidebar-link-modern logout btn__logout">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </a>
    </nav>
</aside>
<div class="sidebar-overlay"></div>
