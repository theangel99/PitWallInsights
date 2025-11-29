<?php
$pageTitle = 'Articles Management';
require_once 'includes/header.php';
require_once dirname(__DIR__) . '/includes/db.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$articleId = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_article'])) {
        $title = $_POST['title'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $excerpt = $_POST['excerpt'] ?? '';
        $content = $_POST['content'] ?? '';
        $category = $_POST['category'] ?? 'novice';
        $featured = isset($_POST['featured']) ? 1 : 0;
        $published = isset($_POST['published']) ? 1 : 0;
        $imageUrl = $_POST['image_url'] ?? '';
        $readTime = $_POST['read_time'] ?? 5;

        try {
            if ($articleId) {
                // Update existing article
                $stmt = $db->prepare("
                    UPDATE articles
                    SET title = ?, slug = ?, excerpt = ?, content = ?, category = ?,
                        featured = ?, published = ?, image_url = ?, read_time = ?,
                        published_at = IF(published = 1 AND published_at IS NULL, NOW(), published_at)
                    WHERE id = ?
                ");
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $featured, $published, $imageUrl, $readTime, $articleId]);
                $message = 'Article updated successfully!';
            } else {
                // Create new article
                $stmt = $db->prepare("
                    INSERT INTO articles (title, slug, excerpt, content, category, featured, published, image_url, read_time, author_id, published_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, IF(? = 1, NOW(), NULL))
                ");
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $featured, $published, $imageUrl, $readTime, Auth::getUserId(), $published]);
                $message = 'Article created successfully!';
                $articleId = $db->lastInsertId();
            }
            $action = 'edit';
        } catch (PDOException $e) {
            $error = 'Error saving article: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_article'])) {
        try {
            $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
            $stmt->execute([$articleId]);
            $message = 'Article deleted successfully!';
            $action = 'list';
            $articleId = null;
        } catch (PDOException $e) {
            $error = 'Error deleting article: ' . $e->getMessage();
        }
    }
}

// Load article for editing
$article = null;
if ($action === 'edit' && $articleId) {
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$articleId]);
    $article = $stmt->fetch();

    if (!$article) {
        $error = 'Article not found!';
        $action = 'list';
    }
}

// List view
if ($action === 'list') {
    $articles = $db->query("
        SELECT a.*, u.username
        FROM articles a
        LEFT JOIN users u ON a.author_id = u.id
        ORDER BY a.created_at DESC
    ")->fetchAll();
}
?>

<?php if ($message): ?>
<div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; color: #10b981;">
    ✓ <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div style="background: rgba(255, 24, 1, 0.1); border: 1px solid var(--f1-red); border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem; color: var(--f1-red);">
    ✗ <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- Articles List -->
    <div style="margin-bottom: 2rem;">
        <a href="?action=new" class="btn btn-primary">+ New Article</a>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Views</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($articles)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No articles yet. Create your first article!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $art): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($art['title']); ?></strong>
                            <?php if ($art['featured']): ?>
                                <span class="badge badge-warning" style="margin-left: 0.5rem;">Featured</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge badge-info"><?php echo htmlspecialchars($art['category']); ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($art['username'] ?? 'Unknown'); ?></td>
                        <td>
                            <?php if ($art['published']): ?>
                                <span class="badge badge-success">Published</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Draft</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($art['views']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($art['created_at'])); ?></td>
                        <td class="table-actions">
                            <a href="?action=edit&id=<?php echo $art['id']; ?>" class="btn btn-secondary btn-small">Edit</a>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this article?');">
                                <input type="hidden" name="delete_article" value="1">
                                <button type="submit" class="btn btn-danger btn-small">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <!-- Article Form (New/Edit) -->
    <div style="margin-bottom: 2rem;">
        <a href="?action=list" class="btn btn-secondary">← Back to Articles</a>
    </div>

    <form method="POST" class="form-container">
        <input type="hidden" name="save_article" value="1">

        <div class="form-row">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="title">Article Title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="form-input"
                    value="<?php echo htmlspecialchars($article['title'] ?? ''); ?>"
                    required
                    onkeyup="generateSlug()"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="slug">URL Slug *</label>
                <input
                    type="text"
                    id="slug"
                    name="slug"
                    class="form-input"
                    value="<?php echo htmlspecialchars($article['slug'] ?? ''); ?>"
                    required
                    pattern="[a-z0-9-]+"
                    title="Only lowercase letters, numbers, and hyphens"
                >
                <small style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                    URL-friendly version (e.g., verstappen-wins-championship)
                </small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="category">Category *</label>
                <select id="category" name="category" class="form-select" required>
                    <option value="novice" <?php echo ($article['category'] ?? '') === 'novice' ? 'selected' : ''; ?>>Novice</option>
                    <option value="analize" <?php echo ($article['category'] ?? '') === 'analize' ? 'selected' : ''; ?>>Analize</option>
                    <option value="intervjuji" <?php echo ($article['category'] ?? '') === 'intervjuji' ? 'selected' : ''; ?>>Intervjuji</option>
                    <option value="tehnologija" <?php echo ($article['category'] ?? '') === 'tehnologija' ? 'selected' : ''; ?>>Tehnologija</option>
                    <option value="ekipe" <?php echo ($article['category'] ?? '') === 'ekipe' ? 'selected' : ''; ?>>Ekipe</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="read_time">Read Time (minutes)</label>
                <input
                    type="number"
                    id="read_time"
                    name="read_time"
                    class="form-input"
                    value="<?php echo htmlspecialchars($article['read_time'] ?? '5'); ?>"
                    min="1"
                    max="60"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="image_url">Image URL</label>
                <input
                    type="url"
                    id="image_url"
                    name="image_url"
                    class="form-input"
                    value="<?php echo htmlspecialchars($article['image_url'] ?? ''); ?>"
                    placeholder="https://example.com/image.jpg"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="excerpt">Excerpt</label>
                <textarea
                    id="excerpt"
                    name="excerpt"
                    class="form-textarea"
                    rows="3"
                    maxlength="500"
                    data-max-length="500"
                ><?php echo htmlspecialchars($article['excerpt'] ?? ''); ?></textarea>
                <small style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem; display: block;">
                    Short summary displayed in article previews (max 500 characters)
                </small>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="content">Content *</label>
                <textarea
                    id="content"
                    name="content"
                    class="form-textarea"
                    rows="15"
                    required
                ><?php echo htmlspecialchars($article['content'] ?? ''); ?></textarea>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="featured"
                        value="1"
                        <?php echo ($article['featured'] ?? false) ? 'checked' : ''; ?>
                        style="width: auto;"
                    >
                    <span class="form-label" style="margin: 0;">Featured Article</span>
                </label>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="published"
                        value="1"
                        <?php echo ($article['published'] ?? false) ? 'checked' : ''; ?>
                        style="width: auto;"
                    >
                    <span class="form-label" style="margin: 0;">Published</span>
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <?php echo $articleId ? 'Update Article' : 'Create Article'; ?>
            </button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <script>
        function generateSlug() {
            const title = document.getElementById('title').value;
            const slug = title
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim();
            document.getElementById('slug').value = slug;
        }
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
