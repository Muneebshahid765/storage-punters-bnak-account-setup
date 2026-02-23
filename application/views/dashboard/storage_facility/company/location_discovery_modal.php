<!-- AI Location Discovery Modal -->
<div class="modal fade" id="locationDiscoveryModal" tabindex="-1" aria-labelledby="locationDiscoveryModalLabel" aria-hidden="true">
    <style>
        .btn-emerald {
            background-color: #0d3b2e;
            color: #ffffff;
            border: none;
        }
        .btn-emerald:hover {
            background-color: #1a5c4a;
            color: #ffffff;
        }
        .btn-emerald:disabled {
            background-color: #a0aec0;
            cursor: not-allowed;
            color: #eeeeee;
        }
        .fw-600 { font-weight: 600; }
    </style>
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #0d3b2e; color: white;">
                <h5 class="modal-title" id="locationDiscoveryModalLabel">
                    <i class="bi bi-robot me-2"></i>AI Location Discovery
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="paste-input-container">
                    <div class="mb-3">
                        <label class="form-label fw-600">Paste your location list from Google Business Profile:</label>
                        <textarea id="paste-input" class="form-control border rounded-3 p-3" rows="12" 
                            placeholder="Example:&#10;Mammoth Industrial Park&#10;7172 Bruce Highway, Forest Glen Queensland 4556, Australia&#10;Verified"></textarea>
                        <div class="form-text mt-2 text-muted">
                            <i class="bi bi-lightbulb me-1"></i>
                            <strong>How to use:</strong> Open your "Your profiles" page on Google, copy your list of locations, and paste it directly above. AI will extract names, addresses, phone numbers, and office hours.
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-emerald px-5 py-2 fw-600" id="btn-parse-paste" disabled>
                            <i class="bi bi-magic me-2"></i>Parse and Verify with AI
                        </button>
                    </div>
                </div>

                <!-- Loading State -->
                <div id="discovery-loading" class="d-none text-center py-5">
                    <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="fw-bold">AI is Processing...</h5>
                    <p class="text-muted" id="loading-text">Extracting details and verifying location information</p>
                </div>

                <!-- Error State -->
                <div id="discovery-error" class="d-none text-center py-5">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold mt-3">Discovery Failed</h5>
                    <p class="text-muted" id="error-message"></p>
                    <button type="button" class="btn btn-primary" id="btn-retry-discovery">
                        <i class="bi bi-arrow-clockwise me-2"></i>Retry
                    </button>
                </div>

                <!-- Results State -->
                <div id="discovery-results" class="d-none">
                    <div class="alert alert-info mb-3 d-flex align-items-center">
                        <i class="bi bi-check2-circle fs-4 me-3"></i>
                        <div>
                            <strong>Ready to Onboard:</strong> AI has verified the following locations. Review them before finalizing.
                        </div>
                    </div>

                    <div class="table-responsive border rounded-3 shadow-sm">
                        <table class="table table-hover mb-0">
                            <thead class="table-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-3 py-3">Facility Name</th>
                                    <th class="py-3">Address</th>
                                    <th class="py-3">Phone</th>
                                    <th class="py-3">Website</th>
                                    <th class="py-3 pe-3">Office Hours</th>
                                </tr>
                            </thead>
                            <tbody id="locations-table-body">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top p-3">
                <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn text-white px-4" id="btn-onboard-all" style="background-color: #0d3b2e;" disabled>
                    <i class="bi bi-cloud-arrow-up me-2"></i>Onboard All Locations
                </button>
            </div>
        </div>
    </div>
</div>
