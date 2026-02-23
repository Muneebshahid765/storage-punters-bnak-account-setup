/**
 * Storage Punters - Giveaway Countdown & Home Page Integration
 */
(function() {
    'use strict';

    var countdownInterval = null;

    function init() {
        fetchCountdownData();
        // Refresh every 5 minutes
        setInterval(fetchCountdownData, 300000);
    }

    function fetchCountdownData() {
        var url = (typeof SITE_URL !== 'undefined' ? SITE_URL : '/') + 'api/subscription/countdown';
        
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    updateUI(data.data);
                }
            })
            .catch(function(err) {
                console.log('Countdown fetch error:', err);
            });
    }

    function updateUI(data) {
        // Update jackpot text
        var jackpotEl = document.getElementById('sp-home-jackpot-text');
        if (jackpotEl && data.jackpot_text) {
            jackpotEl.textContent = data.jackpot_text + '.';
        }

        var vaultAmountEl = document.getElementById('sp-home-vault-amount');
        if (vaultAmountEl && data.draw && data.draw.prize_value) {
            vaultAmountEl.textContent = data.draw.prize_value + ' CASH';
        }

        if (data.draw) {
            // Update draw date
            var drawDateEl = document.getElementById('sp-home-draw-date');
            if (drawDateEl) {
                drawDateEl.textContent = data.draw.draw_date_formatted;
            }

            // Update extra prizes
            var prizesEl = document.getElementById('sp-home-extra-prizes');
            if (prizesEl) {
                var html = '';
                if (data.draw.prize_2nd) {
                    html += '<span>2nd: ' + data.draw.prize_2nd + '</span>';
                }
                if (data.draw.prize_3rd) {
                    html += '<span>3rd: ' + data.draw.prize_3rd + '</span>';
                }
                if (html) prizesEl.innerHTML = html;
            }

            // Start countdown
            startCountdown(data.draw.seconds_remaining);
        }
    }

    function startCountdown(totalSeconds) {
        if (countdownInterval) {
            clearInterval(countdownInterval);
        }

        var remaining = totalSeconds;

        function tick() {
            if (remaining <= 0) {
                clearInterval(countdownInterval);
                setCountdownValues(0, 0, 0, 0);
                return;
            }

            var days = Math.floor(remaining / 86400);
            var hours = Math.floor((remaining % 86400) / 3600);
            var mins = Math.floor((remaining % 3600) / 60);
            var secs = remaining % 60;

            setCountdownValues(days, hours, mins, secs);
            remaining--;
        }

        tick();
        countdownInterval = setInterval(tick, 1000);
    }

    function setCountdownValues(d, h, m, s) {
        var pad = function(n) { return n < 10 ? '0' + n : '' + n; };

        // Home page countdown
        var daysEl = document.getElementById('sp-home-days');
        var hoursEl = document.getElementById('sp-home-hours');
        var minsEl = document.getElementById('sp-home-mins');
        var secsEl = document.getElementById('sp-home-secs');

        if (daysEl) daysEl.textContent = pad(d);
        if (hoursEl) hoursEl.textContent = pad(h);
        if (minsEl) minsEl.textContent = pad(m);
        if (secsEl) secsEl.textContent = pad(s);

        // Vault page countdown (if exists)
        var vDays = document.getElementById('vault-days');
        var vHours = document.getElementById('vault-hours');
        var vMins = document.getElementById('vault-mins');
        var vSecs = document.getElementById('vault-secs');

        if (vDays) vDays.textContent = pad(d);
        if (vHours) vHours.textContent = pad(h);
        if (vMins) vMins.textContent = pad(m);
        if (vSecs) vSecs.textContent = pad(s);
    }

    // Initialize when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
