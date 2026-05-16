<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Support Tickets';

// Fetch tickets
$statusFilter = $_GET['status'] ?? '';

$sql = "SELECT st.*, u.name as user_name
        FROM support_tickets st
        LEFT JOIN users u ON st.user_id = u.id
        WHERE 1=1";

$params = [];

if ($statusFilter) {
    $sql .= " AND st.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY st.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$tickets = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-headset text-primary"></i> Support Tickets
        </h1>
    </div>
    
    <!-- Filter Bar -->
    <div class="filter-bar">
        <div class="row g-3">
            <div class="col-md-4">
                <select class="form-select" onchange="window.location.href='support.php?status=' + this.value">
                    <option value="">All Status</option>
                    <option value="open" <?php echo $statusFilter === 'open' ? 'selected' : ''; ?>>Open</option>
                    <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="closed" <?php echo $statusFilter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                </select>
            </div>
        </div>
    </div>
    
    <!-- Tickets Table -->
    <div class="card shadow-custom">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Subject</th>
                            <th>Customer</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tickets)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-headset fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No support tickets found</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                            <tr>
                                <td><strong>#<?php echo $ticket['id']; ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ticket['subject']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars(substr($ticket['message'], 0, 60)) . '...'; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($ticket['name']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($ticket['email']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $ticket['priority'] === 'high' ? 'danger' : 
                                            ($ticket['priority'] === 'medium' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($ticket['priority']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $ticket['status'] === 'open' ? 'success' : 
                                            ($ticket['status'] === 'in_progress' ? 'warning' : 'secondary'); 
                                    ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $ticket['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDateTime($ticket['created_at']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="ticket-detail.php?id=<?php echo $ticket['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($ticket['status'] !== 'closed'): ?>
                                        <button onclick="updateTicketStatus(<?php echo $ticket['id']; ?>, 'closed')" 
                                                class="btn btn-sm btn-outline-success" title="Close Ticket">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <?php if (!empty($tickets)): ?>
    <div class="row g-4 mt-4">
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?php echo count($tickets); ?></h3>
                    <p class="text-muted mb-0">Total Tickets</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        <?php 
                        $openTickets = count(array_filter($tickets, fn($t) => $t['status'] === 'open'));
                        echo $openTickets;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Open Tickets</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        <?php 
                        $inProgress = count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress'));
                        echo $inProgress;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">In Progress</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-custom">
                <div class="card-body text-center">
                    <h3 class="text-secondary">
                        <?php 
                        $closedTickets = count(array_filter($tickets, fn($t) => $t['status'] === 'closed'));
                        echo $closedTickets;
                        ?>
                    </h3>
                    <p class="text-muted mb-0">Closed Tickets</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateTicketStatus(ticketId, status) {
    if (!confirm(`Are you sure you want to ${status} this ticket?`)) {
        return;
    }
    
    fetch('api/update-ticket-status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            ticket_id: ticketId, 
            status: status 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Success', 'Ticket status updated', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showToast('Error', data.message || 'Failed to update ticket', 'danger');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error', 'An error occurred', 'danger');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
