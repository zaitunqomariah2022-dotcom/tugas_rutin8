
<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php?status=invalid');
    exit;
}

// Mengambil data produk
$stmt = $db->prepare("
    SELECT id, nama_produk, harga, stok
    FROM produk
    WHERE id = :id
");

$stmt->execute(['id' => $id]);
$produk = $stmt->fetch();

if (!$produk) {
    header('Location: index.php?status=notfound');
    exit;
}

$error = '';

// Hapus hanya jika formulir konfirmasi dikirim menggunakan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['konfirmasi'] ?? '') !== 'ya') {
        header('Location: index.php');
        exit;
    }

    try {
        $delete = $db->prepare("
            DELETE FROM produk
            WHERE id = :id
        ");

        $delete->execute(['id' => $id]);

        if ($delete->rowCount() > 0) {
            header('Location: index.php?status=deleted');
            exit;
        }

        $error = 'Produk tidak berhasil dihapus.';
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        $error = 'Produk gagal dihapus. Periksa relasi data pada database.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Konfirmasi Hapus Produk</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 25px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 550px;
            margin: 60px auto;
        }

        .card {
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #b91c1c;
        }

        .warning {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            background: #fee2e2;
            color: #991b1b;
            line-height: 1.6;
        }

        .details {
            margin: 20px 0;
            line-height: 1.9;
        }

        .button {
            display: inline-block;
            padding: 11px 16px;
            border: none;
            border-radius: 7px;
            background: #64748b;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .danger {
            background: #dc2626;
        }

        .error {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 7px;
            background: #fee2e2;
            color: #991b1b;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="card">

        <h1>⚠️ Hapus Produk</h1>

        <p>Apakah kamu yakin ingin menghapus produk ini?</p>

        <div class="warning">
            Tindakan ini akan menghapus data produk dari database
            dan tidak dapat dibatalkan.
        </div>

        <div class="details">
            <strong>Nama:</strong>
            <?= e($produk['nama_produk']) ?><br>

            <strong>Harga:</strong>
            Rp <?= number_format(
                (float) $produk['harga'],
                0,
                ',',
                '.'
            ) ?><br>

            <strong>Stok:</strong>
            <?= (int) $produk['stok'] ?>
        </div>

        <?php if ($error !== ''): ?>
            <div class="error">
                <?= e($error) ?>
            </div>
        <?php endif; ?>

        <div class="actions">
            <form method="POST" action="delete.php?id=<?= (int) $id ?>">
                <input type="hidden" name="konfirmasi" value="ya">

                <button type="submit" class="button danger">
                    Ya, Hapus Produk
                </button>
            </form>

            <a href="index.php" class="button">
                Batal
            </a>
        </div>

    </div>
</div>
</body>
</html>
