<?php
$pageTitle = 'Dashboard';
require_once 'includes/header.php';
require_once dirname(__DIR__) . '/includes/db.php';

$db = getDB();

// Get statistics
try {
    $articlesCount = $db->query("SELECT COUNT(*) FROM articles")->fetchColumn();
    $publishedArticles = $db->query("SELECT COUNT(*) FROM articles WHERE published = 1")->fetchColumn();
    $racesCount = $db->query("SELECT COUNT(*) FROM races WHERE season = " . date('Y'))->fetchColumn();
    $driversCount = $db->query("SELECT COUNT(*) FROM drivers")->fetchColumn();

    // Recent articles
    $recentArticles = $db->query("
        SELECT a.*, u.username
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 5
    ")->fetchAll();

    // Upcoming races
    $upcomingRaces = $db->query("
        SELECT *
        FROM races
        WHERE date >= CURDATE()
        ORDER BY date ASC
        LIMIT 3
    ")->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!-- Statistics Cards -->
<div class="stat-cards">
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Total Articles</span>
            <span class="stat-card-icon">📝</span>
        </div>
        <div class="stat-card-value"><?php echo number_format($articlesCount ?? 0); ?></div>
        <div class="stat-card-subtitle"><?php echo number_format($publishedArticles ?? 0); ?> published</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Races (<?php echo date('Y'); ?>)</span>
            <span class="stat-card-icon">🏁</span>
        </div>
        <div class="stat-card-value"><?php echo number_format($racesCount ?? 0); ?></div>
        <div class="stat-card-subtitle">This season</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Drivers</span>
            <span class="stat-card-icon">👤</span>
        </div>
        <div class="stat-card-value"><?php echo number_format($driversCount ?? 0); ?></div>
        <div class="stat-card-subtitle">Active drivers</div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Quick Actions</span>
            <span class="stat-card-icon">⚡</span>
        </div>
        <div style="margin-top: 1rem;">
            <a href="articles.php?action=new" class="btn btn-primary btn-small" style="width: 100%; justify-content: center;">+ New Article</a>
        </div>
    </div>
</div>

<!-- Recent Articles -->
<?php if (!empty($recentArticles)): ?>
<div style="margin-top: 2.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem; font-family: 'Rajdhani', sans-serif;">Recent Articles</h2>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentArticles as $article): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($article['title']); ?></strong>
                    </td>
                    <td>
                        <span class="badge badge-info"><?php echo htmlspecialchars($article['category']); ?></span>
                    </td>
                    <td><?php echo htmlspecialchars($article['username'] ?? 'Unknown'); ?></td>
                    <td>
                        <?php if ($article['published']): ?>
                            <span class="badge badge-success">Published</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d.m.Y', strtotime($article['created_at'])); ?></td>
                    <td class="table-actions">
                        <a href="articles.php?action=edit&id=<?php echo $article['id']; ?>" class="btn btn-secondary btn-small">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Races -->
<?php if (!empty($upcomingRaces)): ?>
<div style="margin-top: 2.5rem;">
    <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin-bottom: 1rem; font-family: 'Rajdhani', sans-serif;">Upcoming Races</h2>
    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Round</th>
                    <th>Grand Prix</th>
                    <th>Circuit</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($upcomingRaces as $race): ?>
                <tr>
                    <td><strong>R<?php echo $race['round']; ?></strong></td>
                    <td><?php echo htmlspecialchars($race['name']); ?></td>
                    <td><?php echo htmlspecialchars($race['circuit_name']); ?></td>
                    <td><?php echo date('d.m.Y', strtotime($race['date'])); ?></td>
                    <td>
                        <?php if ($race['completed']): ?>
                            <span class="badge badge-success">Completed</span>
                        <?php else: ?>
                            <span class="badge badge-info">Upcoming</span>
                        <?php endif; ?>
                    </td>
                    <td class="table-actions">
                        <a href="races.php?action=edit&id=<?php echo $race['id']; ?>" class="btn btn-secondary btn-small">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div style="background: rgba(255, 24, 1, 0.1); border: 1px solid var(--f1-red); border-radius: 12px; padding: 1.5rem; margin-top: 2rem; color: var(--f1-red);">
    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
