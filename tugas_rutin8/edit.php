
<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header('Location: index.php?status=invalid');
    exit;
}

// Ambil data produk yang akan diedit
$stmt = $db->prepare("
    SELECT id, nama_produk, kategori_id, supplier_id, harga, stok
    FROM produk
    WHERE id = :id
");

$stmt->execute(['id' => $id]);
$produk = $stmt->fetch();

if (!$produk) {
    header('Location: index.php?status=notfound');
    exit;
}

// Ambil data dropdown
$kategori = $db->query("
    SELECT id, nama_kategori
    FROM kategori
    ORDER BY nama_kategori
")->fetchAll();

$supplier = $db->query("
    SELECT id, nama_supplier
    FROM supplier
    ORDER BY nama_supplier
")->fetchAll();

$errors = [];

$nama_produk = $produk['nama_produk'];
$kategori_id = (string) $produk['kategori_id'];
$supplier_id = (string) $produk['supplier_id'];
$harga = (string) $produk['harga'];
$stok = (string) $produk['stok'];

// Proses ketika formulir disimpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $kategori_id = $_POST['kategori_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? '';
    $harga = trim($_POST['harga'] ?? '');
    $stok = trim($_POST['stok'] ?? '');

    if ($nama_produk === '' || strlen($nama_produk) > 150) {
        $errors[] = 'Nama produk wajib diisi dan maksimal 150 karakter.';
    }

    $kategoriIds = array_map(
        'intval',
        array_column($kategori, 'id')
    );

    $supplierIds = array_map(
        'intval',
        array_column($supplier, 'id')
    );

    if (
        !ctype_digit((string) $kategori_id) ||
        !in_array((int) $kategori_id, $kategoriIds, true)
    ) {
        $errors[] = 'Pilih kategori yang valid.';
    }

    if (
        !ctype_digit((string) $supplier_id) ||
        !in_array((int) $supplier_id, $supplierIds, true)
    ) {
        $errors[] = 'Pilih supplier yang valid.';
    }

    if (!is_numeric($harga) || (float) $harga < 0) {
        $errors[] = 'Harga harus berupa angka dan tidak boleh negatif.';
    }

    if (
        filter_var($stok, FILTER_VALIDATE_INT) === false ||
        (int) $stok < 0
    ) {
        $errors[] = 'Stok harus berupa bilangan bulat minimal 0.';
    }

    if (!$errors) {
        try {
            $update = $db->prepare("
                UPDATE produk
                SET nama_produk = :nama_produk,
                    kategori_id = :kategori_id,
                    supplier_id = :supplier_id,
                    harga = :harga,
                    stok = :stok
                WHERE id = :id
            ");

            $update->execute([
                'nama_produk' => $nama_produk,
                'kategori_id' => (int) $kategori_id,
                'supplier_id' => (int) $supplier_id,
                'harga' => $harga,
                'stok' => (int) $stok,
                'id' => $id
            ]);

            header('Location: index.php?status=updated');
            exit;
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors[] = 'Data gagal diperbarui. Silakan coba kembali.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Inventaris</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 700px;
            margin: 20px auto;
        }

        .card {
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.07);
        }

        h1 {
            margin-top: 0;
        }

        .subtitle {
            margin-bottom: 25px;
            color: #64748b;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: white;
            font-size: 15px;
        }

        .button {
            display: inline-block;
            padding: 11px 17px;
            border: none;
            border-radius: 7px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }

        .secondary {
            background: #64748b;
        }

        .error {
            margin-bottom: 20px;
            padding: 15px;
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

        <h1>Edit Produk</h1>

        <p class="subtitle">
            Perbarui informasi produk inventaris.
        </p>

        <?php if ($errors): ?>
            <div class="error">
                <strong>Periksa kembali data berikut:</strong>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit.php?id=<?= (int) $id ?>">

            <div class="form-group">
                <label for="nama_produk">Nama Produk</label>
                <input
                    type="text"
                    id="nama_produk"
                    name="nama_produk"
                    maxlength="150"
                    value="<?= e($nama_produk) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="kategori_id">Kategori</label>
                <select id="kategori_id" name="kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori as $item): ?>
                        <option
                            value="<?= (int) $item['id'] ?>"
                            <?= (string) $kategori_id === (string) $item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nama_kategori']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="supplier_id">Supplier</label>
                <select id="supplier_id" name="supplier_id" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($supplier as $item): ?>
                        <option
                            value="<?= (int) $item['id'] ?>"
                            <?= (string) $supplier_id === (string) $item['id'] ? 'selected' : '' ?>
                        >
                            <?= e($item['nama_supplier']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="harga">Harga (Rp)</label>
                <input
                    type="number"
                    id="harga"
                    name="harga"
                    min="0"
                    step="0.01"
                    value="<?= e($harga) ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="stok">Stok</label>
                <input
                    type="number"
                    id="stok"
                    name="stok"
                    min="0"
                    step="1"
                    value="<?= e($stok) ?>"
                    required
                >
            </div>

            <div class="actions">
                <button type="submit" class="button">
                    Simpan Perubahan
                </button>

                <a href="index.php" class="button secondary">
                    Batal
                </a>
            </div>

        </form>
    </div>
</div>

</body>
</html>
