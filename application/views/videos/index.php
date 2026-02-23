<!-- Style Block -->
<style>
/* Theme Variables */
:root {
    --bg-dark-base: #0b0f19;
    --bg-dark-surface: #131720;
    --text-gold: #ffc107;
}

/* Force dark background on the wrapper */
.video-page-wrapper {
    background-color: var(--bg-dark-base);
    color: #ffffff;
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    width: 100%;
    margin-top: -20px; /* Counteract default padding if any */
    padding-bottom: 5rem;
}

.video-hero {
    background: radial-gradient(circle at 70% 30%, #1a2c24 0%, #0b0f19 70%);
    padding: 100px 0 60px;
    position: relative;
    overflow: hidden;
}

.video-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url('<?= base_url("assets/images/pattern_overlay.png") ?>');
    opacity: 0.05;
    pointer-events: none;
}

.season-title {
    font-size: 1.75rem;
    font-weight: 800;
    color: white;
    margin-bottom: 2rem;
    border-left: 5px solid var(--text-gold);
    padding-left: 1rem;
    display: flex;
    align-items: center;
}

.video-card {
    background: var(--bg-dark-surface);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%;
    cursor: pointer;
}

.video-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    border-color: rgba(255, 193, 7, 0.3);
}

.thumbnail-container {
    position: relative;
    padding-top: 56.25%; /* 16:9 Aspect Ratio */
    background: #000;
    overflow: hidden;
}

.thumbnail-container img {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
    opacity: 0.8;
}

.video-card:hover .thumbnail-container img {
    transform: scale(1.05);
    opacity: 1;
}

.play-button {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%) scale(0.9);
    width: 60px; height: 60px;
    background: rgba(255, 193, 7, 0.9);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 0 20px rgba(0,0,0,0.5);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 2;
}

.video-card:hover .play-button {
    transform: translate(-50%, -50%) scale(1.1);
    background: #ffc107;
}

.play-button i {
    font-size: 2rem;
    color: #000;
    margin-left: 4px; /* Optical adjustment */
}

.video-info {
    padding: 1.5rem;
}

.video-title {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.4;
    margin-bottom: 0.5rem;
    color: white;
}

.video-meta {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.5);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.badge-new {
    background: #dc3545;
    color: white;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 0.25em 0.6em;
    border-radius: 4px;
    letter-spacing: 0.5px;
    position: absolute;
    top: 10px; left: 10px;
    z-index: 5;
    box-shadow: 0 2px 5px rgba(0,0,0,0.3);
}

.load-skeleton {
    animation: pulse 1.5s infinite;
    background: linear-gradient(90deg, #131720 0%, #1a202c 50%, #131720 100%);
    background-size: 200% 100%;
}

@keyframes pulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<div class="video-page-wrapper">
    <!-- Hero Section -->
    <section class="video-hero">
        <div class="container text-center">
            <h6 class="text-warning text-uppercase fw-bold mb-3" style="letter-spacing: 2px;">
                <i class="bi bi-play-circle-fill me-2"></i>Official Channel
            </h6>
            <h1 class="display-3 fw-black text-white mb-4" style="font-weight: 900; letter-spacing: -1px;">
                STORAGE PUNTERS <span class="text-warning fst-italic">TV</span>
            </h1>
            <p class="lead text-secondary mx-auto" style="max-width: 600px;">
                Follow the team as we hunt for treasure in abandoned storage units. Real auctions, real risks, and massive finds.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container pb-5">
        <div id="video-content-area">
            <!-- Skeleton Loading State -->
            <div class="row g-4 mb-5">
                <div class="col-12"><div class="load-skeleton rounded" style="height: 40px; width: 200px;"></div></div>
                <div class="col-md-4"><div class="load-skeleton rounded-3" style="aspect-ratio: 16/9; width: 100%;"></div></div>
                <div class="col-md-4"><div class="load-skeleton rounded-3" style="aspect-ratio: 16/9; width: 100%;"></div></div>
                <div class="col-md-4"><div class="load-skeleton rounded-3" style="aspect-ratio: 16/9; width: 100%;"></div></div>
            </div>
        </div>
    </div>
</div>

<!-- Video Modal -->
<div class="modal fade" id="videoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-black border-0">
            <div class="modal-header border-0 position-absolute top-0 end-0 z-3">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="ratio ratio-16x9">
                    <iframe id="videoPlayer" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    fetchVideos();
});

function fetchVideos() {
    fetch('<?= site_url("api/videos/list") ?>')
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                renderLibrary(res.data);
            }
        })
        .catch(err => console.error('Error fetching videos:', err));
}

function renderLibrary(seasons) {
    const container = document.getElementById('video-content-area');
    
    if (seasons.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="bi bi-camera-video display-1 text-secondary opacity-25 mb-3"></i>
                <h4 class="text-secondary">No episodes found.</h4>
            </div>
        `;
        return;
    }

    container.innerHTML = seasons
        .filter(season => season.episodes && season.episodes.length > 0)
        .map((season, index) => `
        <div class="mb-5 animate__animated animate__fadeInUp" style="animation-delay: ${index * 0.1}s">
            <div class="season-title">
                ${season.season_name}
                <span class="badge bg-dark border border-secondary text-secondary ms-3 fw-normal" style="font-size: 0.8rem;">
                    ${season.episodes.length} Episodes
                </span>
            </div>
            
            <div class="row g-4">
                ${season.episodes.map(ep => {
                    const youtubeId = getYoutubeId(ep.youtube_url);
                    const thumbUrl = `https://img.youtube.com/vi/${youtubeId}/maxresdefault.jpg`;
                    const isNew = isRecent(ep.created_at);
                    
                    return `
                        <div class="col-md-6 col-lg-4">
                            <div class="video-card" onclick="openVideo('${youtubeId}')">
                                <div class="thumbnail-container">
                                    <img src="${thumbUrl}" alt="${ep.episode_name}" loading="lazy">
                                    <div class="play-button">
                                        <i class="bi bi-play-fill"></i>
                                    </div>
                                    ${isNew ? '<span class="badge-new">NEW DROP</span>' : ''}
                                </div>
                                <div class="video-info">
                                    <h5 class="video-title text-truncate">${ep.episode_name}</h5>
                                    <div class="video-meta">
                                        <span><i class="bi bi-calendar-event me-1"></i> ${new Date(ep.created_at).toLocaleDateString()}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        </div>
    `).join('');
}

function getYoutubeId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

function isRecent(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now - date);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    return diffDays <= 7;
}

function openVideo(videoId) {
    const modal = new bootstrap.Modal(document.getElementById('videoModal'));
    const player = document.getElementById('videoPlayer');
    player.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0`;
    modal.show();
    
    // Stop video when modal closes
    document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
        player.src = "";
    });
}
</script>
