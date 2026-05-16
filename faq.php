<?php
require_once 'config/config.php';
$pageTitle = 'FAQ';

// Fetch FAQs
$faqs = $db->query("SELECT * FROM faqs WHERE status = 'active' ORDER BY display_order, id")->fetchAll();

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center mb-5">
        <div class="col-lg-8 text-center">
            <h1 class="fw-bold mb-3">Frequently Asked Questions</h1>
            <p class="lead text-muted">Find answers to common questions about our digital marketplace</p>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <?php if (empty($faqs)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle fa-3x mb-3"></i>
                <h4>No FAQs Available</h4>
                <p>Please check back later or contact our support team.</p>
            </div>
            <?php else: ?>
            <div class="accordion faq-accordion" id="faqAccordion">
                <?php foreach ($faqs as $index => $faq): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                        <button class="accordion-button <?php echo $index !== 0 ? 'collapsed' : ''; ?>" 
                                type="button" 
                                data-mdb-toggle="collapse" 
                                data-mdb-target="#collapse<?php echo $index; ?>">
                            <?php echo htmlspecialchars($faq['question']); ?>
                        </button>
                    </h2>
                    <div id="collapse<?php echo $index; ?>" 
                         class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" 
                         data-mdb-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?php echo nl2br(htmlspecialchars($faq['answer'])); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Still have questions? -->
            <div class="card shadow-custom rounded-custom mt-5">
                <div class="card-body text-center p-5">
                    <i class="fas fa-question-circle fa-4x text-primary mb-3"></i>
                    <h3>Still have questions?</h3>
                    <p class="text-muted mb-4">Can't find the answer you're looking for? Please contact our support team.</p>
                    <a href="contact.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-envelope me-2"></i> Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
