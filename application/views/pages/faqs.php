<?php
    $faqList = [
        [
            'question' => 'Who is legally responsible for selling the contents of a storage unit at auction?',
            'answer' => '<p>The Storage Facility or Seller listing the auction is solely responsible for the legal right to sell the contents of the unit. The procedures for the sale are conducted according to their signed Storage Agreement and/or relevant local and state laws regarding abandoned goods. Storage Punters acts only as the platform for the auction and assumes no legal responsibility for the sale itself.</p>'
        ],
        [
            'question' => 'What do the terms "Job Lot," "As Is," and "Where Is" mean?',
            'answer' => '<ul><li><strong>"Job Lot"</strong>: You are bidding on the entire contents of the storage unit as a single lot, not individual items.</li><li><strong>"As Is"</strong>: All goods are sold without warranties regarding condition or quality.</li><li><strong>"Where Is"</strong>: Items cannot be shipped. The winning bidder must collect them from the facility location.</li></ul>'
        ],
        [
            'question' => 'Are there any fees in addition to the winning bid?',
            'answer' => '<p>Yes. The final price includes a Buyer\'s Premium of 17.5% added on top of the winning bid. This fee, along with the bid, is inclusive of GST and is payable at the time of purchase.</p>'
        ],
        [
            'question' => 'What is a "Clean Up Deposit"?',
            'answer' => '<p>The buyer is responsible for fully clearing the unit. If the unit is not left empty and clean, the Storage Facility may retain the clean-up deposit (usually listed on the auction page) to cover cleaning and disposal costs.</p>'
        ],
        [
            'question' => 'Can I bid on any auction?',
            'answer' => '<p>Yes, anyone can bid on listed auctions. However, consider your ability to travel and collect items within the timeframe specified (typically 72 hours). You are responsible for clearing the unit.</p>'
        ],
        [
            'question' => 'Can anyone bid on these units?',
            'answer' => '<p>Yes. Any person over 18 years of age with a valid credit card can place bids on the Platform.</p>'
        ],
        [
            'question' => 'Can I have a bid removed?',
            'answer' => '<p>No. Bids are final and cannot be removed once placed. This helps maintain the integrity of the auction process. Bid carefully.</p>'
        ],
        [
            'question' => 'What happens after I win an auction?',
            'answer' => '<p>You will receive a confirmation email with details such as the unit number, facility address, and contact information. Your invoice will also be attached and available under your bidding history. You have 72 hours to contact the facility and collect the goods, unless stated otherwise on the auction page.</p>'
        ],
        [
            'question' => 'Can I get a refund if I do not like what I bought?',
            'answer' => '<p>No. As outlined in our Refund Policy, all sales are final. Goods are sold on an “as is” and “job lot” basis. We recommend inspecting the goods beforehand if possible.</p>'
        ],
        [
            'question' => 'What should I do with personal items found in the unit?',
            'answer' => '<p>Return any personal or sensitive items (documents, passports, photos, etc.) to the staff at the storage facility. If unsure, consult the Seller directly.</p>'
        ],
        [
            'question' => 'How long do I have to remove the goods?',
            'answer' => '<p>You have 72 hours from the notification to clear the unit, unless otherwise stated. Extensions or rental of the unit may be arranged directly with the Seller.</p>'
        ],
        [
            'question' => 'Why do some units disappear before the auction closes?',
            'answer' => '<p>Sellers manage their listings and may remove a unit if the tenant pays their outstanding debt. In such cases, contact the storage facility for more details.</p>'
        ],
        [
            'question' => 'I am a Seller. How and when will I receive the auction proceeds?',
            'answer' => '<p>After the auction closes, you will be notified of the result. We will arrange for payment to your nominated bank account (minus commission). Even if there are no bids, you will be notified.</p>'
        ],
        [
            'question' => 'How is the Seller\'s commission calculated?',
            'answer' => '<p>The Seller\'s commission is a percentage of the final sale price (inclusive of GST) and is defined in your agreement with Storage Punters.</p>'
        ],
        [
            'question' => 'What if I notice a discrepancy between the description and the photos?',
            'answer' => '<p>If you believe there is a mismatch between the description and photos, contact our support team. We will liaise with the Seller for clarification, but the Seller holds the most accurate information.</p>'
        ],
        [
            'question' => 'Who should I contact if I have an issue with my purchase?',
            'answer' => '<p>Speak to the Seller first. They know the contents of the unit. <strong>Important:</strong> Contact them before removing any items, as issues become harder to resolve afterward.</p>'
        ],
    ];
?>

<div class="faq-modern-page container-fluid p-0 d-flex flex-column h-100">
    <main class="faq-modern flex-grow-1">
        <section class="faq-hero">
            <div class="container">
                <div class="faq-hero-card">
                    <div>
                        <h1 class="fw-bold mb-3">Frequently Asked Questions (FAQ)</h1>
                        <p class="mb-0">Welcome to our FAQ section. Below are answers to the most common questions our users ask. If your question is not listed here, please contact us and we will be happy to assist you.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="faq-collection">
            <div class="container">
                <div class="faq-panels">
                    <?php foreach ($faqList as $index => $faq): ?>
                        <details class="faq-item" <?= $index === 0 ? 'open' : ''; ?>>
                            <summary>
                                <span class="faq-index"><?= str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?></span>
                                <span class="faq-question"><?= $faq['question']; ?></span>
                                <span class="faq-icon">
                                    <i class="bi bi-chevron-down"></i>
                                </span>
                            </summary>
                            <div class="faq-answer">
                                <?= $faq['answer']; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </div>
                <p class="text-muted mt-4 small text-center">&copy; 2025 Storage Punters. All Rights Reserved.</p>
            </div>
        </section>
    </main>
</div>