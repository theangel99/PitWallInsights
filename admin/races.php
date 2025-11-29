<?php
$pageTitle = 'Race Management';
require_once 'includes/header.php';
require_once dirname(__DIR__) . '/includes/db.php';

$db = getDB();
$action = $_GET['action'] ?? 'list';
$raceId = $_GET['id'] ?? null;
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_race'])) {
        $season = $_POST['season'] ?? date('Y');
        $round = $_POST['round'] ?? 1;
        $name = $_POST['name'] ?? '';
        $circuitName = $_POST['circuit_name'] ?? '';
        $location = $_POST['location'] ?? '';
        $country = $_POST['country'] ?? '';
        $date = $_POST['date'] ?? '';
        $time = $_POST['time'] ?? null;
        $laps = $_POST['laps'] ?? null;
        $distance = $_POST['distance'] ?? null;
        $completed = isset($_POST['completed']) ? 1 : 0;
        $poleDriverId = $_POST['pole_position_driver_id'] ?? null;
        $fastestLapDriverId = $_POST['fastest_lap_driver_id'] ?? null;
        $fastestLapTime = $_POST['fastest_lap_time'] ?? null;

        try {
            if ($raceId) {
                // Update existing race
                $stmt = $db->prepare("
                    UPDATE races
                    SET season = ?, round = ?, name = ?, circuit_name = ?, location = ?, country = ?,
                        date = ?, time = ?, laps = ?, distance = ?, completed = ?,
                        pole_position_driver_id = ?, fastest_lap_driver_id = ?, fastest_lap_time = ?
                    WHERE id = ?
                ");
                $stmt->execute([$season, $round, $name, $circuitName, $location, $country, $date, $time, $laps, $distance, $completed, $poleDriverId, $fastestLapDriverId, $fastestLapTime, $raceId]);
                $message = 'Race updated successfully!';
            } else {
                // Create new race
                $stmt = $db->prepare("
                    INSERT INTO races (season, round, name, circuit_name, location, country, date, time, laps, distance, completed, pole_position_driver_id, fastest_lap_driver_id, fastest_lap_time)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$season, $round, $name, $circuitName, $location, $country, $date, $time, $laps, $distance, $completed, $poleDriverId, $fastestLapDriverId, $fastestLapTime]);
                $raceId = $db->lastInsertId();
                $message = 'Race created successfully!';
            }
            $action = 'edit';
        } catch (PDOException $e) {
            $error = 'Error saving race: ' . $e->getMessage();
        }
    } elseif (isset($_POST['delete_race'])) {
        try {
            $stmt = $db->prepare("DELETE FROM races WHERE id = ?");
            $stmt->execute([$raceId]);
            $message = 'Race deleted successfully!';
            $action = 'list';
            $raceId = null;
        } catch (PDOException $e) {
            $error = 'Error deleting race: ' . $e->getMessage();
        }
    } elseif (isset($_POST['save_highlights'])) {
        $highlights = $_POST['highlights'] ?? [];

        try {
            // Delete existing highlights
            $stmt = $db->prepare("DELETE FROM race_highlights WHERE race_id = ?");
            $stmt->execute([$raceId]);

            // Insert new highlights
            $stmt = $db->prepare("INSERT INTO race_highlights (race_id, highlight, sort_order) VALUES (?, ?, ?)");
            foreach ($highlights as $index => $highlight) {
                if (!empty(trim($highlight))) {
                    $stmt->execute([$raceId, $highlight, $index]);
                }
            }
            $message = 'Race highlights updated successfully!';
        } catch (PDOException $e) {
            $error = 'Error saving highlights: ' . $e->getMessage();
        }
    }
}

// Load race for editing
$race = null;
$highlights = [];
if ($action === 'edit' && $raceId) {
    $stmt = $db->prepare("SELECT * FROM races WHERE id = ?");
    $stmt->execute([$raceId]);
    $race = $stmt->fetch();

    if (!$race) {
        $error = 'Race not found!';
        $action = 'list';
    } else {
        // Load highlights
        $stmt = $db->prepare("SELECT highlight FROM race_highlights WHERE race_id = ? ORDER BY sort_order");
        $stmt->execute([$raceId]);
        $highlights = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

// Load drivers for dropdowns
$drivers = $db->query("
    SELECT d.*, c.name as constructor_name
    FROM drivers d
    LEFT JOIN constructors c ON d.constructor_id = c.id
    ORDER BY d.last_name
")->fetchAll();

// List view
if ($action === 'list') {
    $season = $_GET['season'] ?? date('Y');
    $races = $db->prepare("
        SELECT r.*,
            p.first_name as pole_first_name, p.last_name as pole_last_name,
            f.first_name as fastest_first_name, f.last_name as fastest_last_name
        FROM races r
        LEFT JOIN drivers p ON r.pole_position_driver_id = p.id
        LEFT JOIN drivers f ON r.fastest_lap_driver_id = f.id
        WHERE r.season = ?
        ORDER BY r.round ASC
    ");
    $races->execute([$season]);
    $races = $races->fetchAll();
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
    <!-- Races List -->
    <div style="margin-bottom: 2rem; display: flex; gap: 1rem; align-items: center;">
        <a href="?action=new" class="btn btn-primary">+ New Race</a>

        <form method="GET" style="display: flex; gap: 0.5rem; align-items: center;">
            <label style="color: var(--text-secondary); font-weight: 600;">Season:</label>
            <select name="season" class="form-select" style="width: auto;" onchange="this.form.submit()">
                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                    <option value="<?php echo $y; ?>" <?php echo $season == $y ? 'selected' : ''; ?>><?php echo $y; ?></option>
                <?php endfor; ?>
            </select>
        </form>
    </div>

    <div class="data-table">
        <table>
            <thead>
                <tr>
                    <th>Round</th>
                    <th>Grand Prix</th>
                    <th>Circuit</th>
                    <th>Date</th>
                    <th>Pole Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($races)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No races for season <?php echo $season; ?>. Create your first race!
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($races as $r): ?>
                    <tr>
                        <td><strong>R<?php echo $r['round']; ?></strong></td>
                        <td><?php echo htmlspecialchars($r['name']); ?></td>
                        <td><?php echo htmlspecialchars($r['circuit_name']); ?></td>
                        <td><?php echo date('d.m.Y', strtotime($r['date'])); ?></td>
                        <td>
                            <?php if ($r['pole_first_name']): ?>
                                <?php echo htmlspecialchars($r['pole_first_name'] . ' ' . $r['pole_last_name']); ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['completed']): ?>
                                <span class="badge badge-success">Completed</span>
                            <?php else: ?>
                                <span class="badge badge-info">Upcoming</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-actions">
                            <a href="?action=edit&id=<?php echo $r['id']; ?>" class="btn btn-secondary btn-small">Edit</a>
                            <a href="results.php?race_id=<?php echo $r['id']; ?>" class="btn btn-secondary btn-small">Results</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php else: ?>
    <!-- Race Form (New/Edit) -->
    <div style="margin-bottom: 2rem;">
        <a href="?action=list" class="btn btn-secondary">← Back to Races</a>
        <?php if ($raceId): ?>
            <a href="results.php?race_id=<?php echo $raceId; ?>" class="btn btn-primary">Manage Results</a>
        <?php endif; ?>
    </div>

    <form method="POST" class="form-container">
        <input type="hidden" name="save_race" value="1">

        <h3 style="color: var(--text-primary); margin-bottom: 1.5rem; font-family: 'Rajdhani', sans-serif;">Race Details</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="season">Season *</label>
                <input
                    type="number"
                    id="season"
                    name="season"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['season'] ?? date('Y')); ?>"
                    min="2020"
                    max="2030"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="round">Round *</label>
                <input
                    type="number"
                    id="round"
                    name="round"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['round'] ?? '1'); ?>"
                    min="1"
                    max="30"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="date">Date *</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['date'] ?? ''); ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="time">Start Time</label>
                <input
                    type="time"
                    id="time"
                    name="time"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['time'] ?? ''); ?>"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group" style="grid-column: 1 / -1;">
                <label class="form-label" for="name">Grand Prix Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['name'] ?? ''); ?>"
                    placeholder="e.g., Velika nagrada Bahrajna"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="circuit_name">Circuit Name *</label>
                <input
                    type="text"
                    id="circuit_name"
                    name="circuit_name"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['circuit_name'] ?? ''); ?>"
                    placeholder="e.g., Bahrain International Circuit"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="location">Location *</label>
                <input
                    type="text"
                    id="location"
                    name="location"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['location'] ?? ''); ?>"
                    placeholder="e.g., Sakhir"
                    required
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="country">Country *</label>
                <input
                    type="text"
                    id="country"
                    name="country"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['country'] ?? ''); ?>"
                    placeholder="e.g., Bahrain"
                    required
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="laps">Number of Laps</label>
                <input
                    type="number"
                    id="laps"
                    name="laps"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['laps'] ?? ''); ?>"
                    min="1"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="distance">Circuit Distance (km)</label>
                <input
                    type="number"
                    id="distance"
                    name="distance"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['distance'] ?? ''); ?>"
                    step="0.001"
                    placeholder="e.g., 5.412"
                >
            </div>
        </div>

        <h3 style="color: var(--text-primary); margin: 2rem 0 1.5rem; font-family: 'Rajdhani', sans-serif;">Race Statistics</h3>

        <div class="form-row">
            <div class="form-group">
                <label class="form-label" for="pole_position_driver_id">Pole Position</label>
                <select id="pole_position_driver_id" name="pole_position_driver_id" class="form-select">
                    <option value="">- Select Driver -</option>
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?php echo $driver['id']; ?>" <?php echo ($race['pole_position_driver_id'] ?? '') == $driver['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                            <?php if ($driver['constructor_name']): ?>
                                (<?php echo htmlspecialchars($driver['constructor_name']); ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="fastest_lap_driver_id">Fastest Lap</label>
                <select id="fastest_lap_driver_id" name="fastest_lap_driver_id" class="form-select">
                    <option value="">- Select Driver -</option>
                    <?php foreach ($drivers as $driver): ?>
                        <option value="<?php echo $driver['id']; ?>" <?php echo ($race['fastest_lap_driver_id'] ?? '') == $driver['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($driver['first_name'] . ' ' . $driver['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="fastest_lap_time">Fastest Lap Time</label>
                <input
                    type="text"
                    id="fastest_lap_time"
                    name="fastest_lap_time"
                    class="form-input"
                    value="<?php echo htmlspecialchars($race['fastest_lap_time'] ?? ''); ?>"
                    placeholder="e.g., 1:35.123"
                >
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="completed"
                        value="1"
                        <?php echo ($race['completed'] ?? false) ? 'checked' : ''; ?>
                        style="width: auto;"
                    >
                    <span class="form-label" style="margin: 0;">Race Completed</span>
                </label>
            </div>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <?php echo $raceId ? 'Update Race' : 'Create Race'; ?>
            </button>
            <a href="?action=list" class="btn btn-secondary">Cancel</a>
            <?php if ($raceId): ?>
                <form method="POST" style="margin-left: auto;" onsubmit="return confirm('Are you sure you want to delete this race?');">
                    <input type="hidden" name="delete_race" value="1">
                    <button type="submit" class="btn btn-danger">Delete Race</button>
                </form>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($raceId): ?>
    <!-- Race Highlights -->
    <form method="POST" class="form-container" style="margin-top: 2rem;">
        <input type="hidden" name="save_highlights" value="1">

        <h3 style="color: var(--text-primary); margin-bottom: 1.5rem; font-family: 'Rajdhani', sans-serif;">Race Highlights</h3>

        <div id="highlights-container">
            <?php
            if (empty($highlights)) {
                $highlights = ['', '', ''];
            }
            foreach ($highlights as $index => $highlight):
            ?>
            <div class="form-group" style="margin-bottom: 1rem;">
                <input
                    type="text"
                    name="highlights[]"
                    class="form-input"
                    value="<?php echo htmlspecialchars($highlight); ?>"
                    placeholder="Highlight <?php echo $index + 1; ?>"
                >
            </div>
            <?php endforeach; ?>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1rem;">
            <button type="button" class="btn btn-secondary btn-small" onclick="addHighlight()">+ Add Highlight</button>
            <button type="submit" class="btn btn-primary">Save Highlights</button>
        </div>
    </form>

    <script>
        function addHighlight() {
            const container = document.getElementById('highlights-container');
            const count = container.children.length + 1;
            const div = document.createElement('div');
            div.className = 'form-group';
            div.style.marginBottom = '1rem';
            div.innerHTML = `
                <input
                    type="text"
                    name="highlights[]"
                    class="form-input"
                    placeholder="Highlight ${count}"
                >
            `;
            container.appendChild(div);
        }
    </script>
    <?php endif; ?>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
