// ============================================================
// 🌏 distance_by_filter.js
// Smart Auction Sorting by Distance (Google Maps version)
// ============================================================

// --- 🗺️ Configuration ---
const GOOGLE_API_KEY = "AIzaSyAVfGb-g1V3gP1rHrvDB7N7HBv9Cmz9sQU"; // Replace with your valid key

// ============================================================
// 🌎 Loader Overlay
// ============================================================

function showLocationLoader(message = "Finding Nearby Storage Units") {
    if (!$("#location-loader-overlay").length) {
        $("body").append(`
            <div id="location-loader-overlay" style="display:none;">
                <div class="location-loader-content">
                    <div class="loader-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <h3 class="loader-title">${message}</h3>
                    <p class="loader-desc">
                        We’re finding the closest units based on your location.<br>
                        Please wait a moment...
                    </p>
                    <div class="loader-dots">
                        <span></span><span></span><span></span>
                    </div>
                </div>
            </div>
        `);

        $("head").append(`
            <style>
            #location-loader-overlay {
                position: fixed;
                inset: 0;
                background: linear-gradient(135deg, rgba(255,255,255,0.97), rgba(240,255,250,0.95));
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                animation: fadeIn 0.4s ease;
            }
            .location-loader-content {
                text-align: center;
                color: #222;
                max-width: 400px;
                animation: pulseIn 0.8s ease;
            }
            .loader-icon {
                font-size: 4rem;
                color: #00a86b;
                animation: bounce 1.2s infinite;
                margin-bottom: 10px;
            }
            .loader-title {
                font-size: 1.6rem;
                font-weight: 600;
                margin-bottom: 8px;
            }
            .loader-desc {
                font-size: 1rem;
                color: #555;
                margin-bottom: 12px;
            }
            .loader-dots span {
                display: inline-block;
                width: 10px;
                height: 10px;
                margin: 0 3px;
                background-color: #00a86b;
                border-radius: 50%;
                animation: dotFlashing 1s infinite alternate;
            }
            .loader-dots span:nth-child(2){ animation-delay:0.2s; }
            .loader-dots span:nth-child(3){ animation-delay:0.4s; }
            @keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
            @keyframes pulseIn{from{transform:scale(0.9);opacity:0;}to{transform:scale(1);opacity:1;}}
            @keyframes bounce{0%,100%{transform:translateY(0);}50%{transform:translateY(-8px);}}
            @keyframes dotFlashing{0%{opacity:0.2;transform:scale(0.9);}100%{opacity:1;transform:scale(1.1);}}
            </style>
        `);
    }

    $("#location-loader-overlay .loader-title").text(message);
    $("#location-loader-overlay").fadeIn(300);
}

function hideLocationLoader() {
    $("#location-loader-overlay").fadeOut(400);
}

// ============================================================
// 🚀 Main Function
// ============================================================

async function applyDistanceFiltersAndSort() {
    console.clear();
    console.log("🚀 [distance_by_filter.js] applyDistanceFiltersAndSort() started");

    showLocationLoader("Finding Nearby Storage Units");

    try {
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get("search_term")?.trim() || "";
        const region = urlParams.get("region")?.trim() || "";
        const state = urlParams.get("state")?.trim() || "";
        const zip = urlParams.get("zip")?.trim() || "";
        const radius = parseFloat(urlParams.get("radius")) || 0;

        console.log("🌐 URL Params:", { searchTerm, region, state, zip, radius });

        const grid = $(".auction__all__live__section__container__grid");
        const cards = grid.children("div[data-facility-post-code]").get();

        if (!cards.length) {
            console.warn("⚠️ No auction cards found. Aborting.");
            hideLocationLoader();
            return;
        }

        // --- 1️⃣ Determine Base Location ---
        let baseCoords = null;
        if (zip && /^\d{4,6}$/.test(zip)) {
            baseCoords = await getCoordsFromAddress(`${zip}, ${state}, ${region}`);
            console.log(`📍 Using ZIP param for base location: ${zip}`, baseCoords);
        } else if (searchTerm && /^[A-Za-z0-9\s]+$/.test(searchTerm)) {
            baseCoords = await getCoordsFromAddress(`${searchTerm}, ${state}, ${region}`);
            console.log(`📍 Using search_term for base location: ${searchTerm}`, baseCoords);
        }

        if (!baseCoords) {
            console.log("🧭 No valid URL filters. Getting current user location...");
            showLocationLoader("Detecting your location...");
            baseCoords = await getCurrentPosition();
        }

        if (!baseCoords) {
            hideLocationLoader();
            console.error("❌ No base coordinates found. Cannot sort.");
            return;
        }

        console.log("📍 Base coordinates:", baseCoords);
        showLocationLoader("Sorting units closest to your location...");
        const cardDistances = [];

        // --- 2️⃣ Resolve Each Card’s Location ---
        for (let card of cards) {
            const street = $(card).data("facility-street-address");
            const city = $(card).data("facility-city");
            const postCode = $(card).data("facility-post-code");
            const address = `${street}, ${city}, ${state || ""}, ${region || ""} ${postCode}`.trim();

            console.log(`🏠 Resolving facility: ${address}`);
            const coords = await getCoordsFromAddress(address);
            if (!coords) {
                console.warn(`⚠️ Skipping: ${address} (no coordinates found)`);
                continue;
            }

            const distance = haversine(baseCoords.lat, baseCoords.lng, coords.lat, coords.lng);
            cardDistances.push({ card, distance });
            console.log(`📦 ${address} → ${distance.toFixed(2)} km away`);
        }

        if (!cardDistances.length) {
            console.warn("⚠️ No distances calculated, aborting.");
            hideLocationLoader();
            return;
        }

        // --- 3️⃣ Optional Radius Filter ---
        let filtered = cardDistances;
        if (radius > 0) {
            filtered = filtered.filter(item => item.distance <= radius);
            console.log(`🔍 Filtered by radius ≤ ${radius} km → ${filtered.length} auctions left`);
        }

        // --- 4️⃣ Sort by Distance ---
        filtered.sort((a, b) => a.distance - b.distance);

        // --- 5️⃣ Re-render Sorted Cards ---
        grid.empty();
        filtered.forEach(({ card, distance }) => {
            $(card).attr("data-distance", distance.toFixed(2));
            $(card).find(".card__info").append(
                `<p class="text-center text-muted mb-2" style="font-size:13px;">📍 ${distance.toFixed(2)} km away</p>`
            );
            grid.append(card);
        });

        hideLocationLoader();
        showSortingMessage("✅ We’ve sorted auctions closest to your location.");
        console.log("✅ Auctions sorted successfully by distance!");
    } catch (err) {
        hideLocationLoader();
        console.error("❌ Error in sorting:", err);
    }
}

// ============================================================
// 📍 Helper Functions
// ============================================================

// Google Maps Geocoding API
async function getCoordsFromAddress(address) {
    try {
        const url = `https://maps.googleapis.com/maps/api/geocode/json?address=${encodeURIComponent(address)}&region=au&key=${GOOGLE_API_KEY}`;
        const res = await fetch(url);
        const data = await res.json();

        if (data.status === "OK" && data.results?.length) {
            const loc = data.results[0].geometry.location;
            return { lat: loc.lat, lng: loc.lng };
        } else {
            console.warn(`⚠️ Geocode failed for: ${address}`, data.status);
        }
    } catch (e) {
        console.error("❌ Google Geocode error:", e);
    }
    return null;
}

// Browser geolocation (fallback)
function getCurrentPosition() {
    return new Promise(resolve => {
        if (!navigator.geolocation) return resolve(null);
        navigator.geolocation.getCurrentPosition(
            pos => resolve({ lat: pos.coords.latitude, lng: pos.coords.longitude }),
            err => {
                console.warn("⚠️ Geolocation denied:", err.message);
                resolve(null);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    });
}

// Haversine formula (km)
function haversine(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(lat1 * Math.PI / 180) *
        Math.cos(lat2 * Math.PI / 180) *
        Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// User feedback banner
function showSortingMessage(message) {
    console.log("💬", message);
    const html = `
        <div id="location-sort-msg"
             class="alert alert-info text-center my-3"
             style="border-radius:8px;">
             <i class="bi bi-geo-alt-fill"></i> ${message}
        </div>`;
    $(".auction__all__live__section__container__grid").before(html);
    setTimeout(() => $("#location-sort-msg").fadeOut(500, function(){ $(this).remove(); }), 7000);
}
