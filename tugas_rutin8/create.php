
<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$errors = [];
$nama_produk = '';
$kategori_id = '';
$supplier_id = '';
$harga = '';
$stok = '';

// Mengambil data kategori dan supplier untuk dropdown
$kategori = $db->query(
    "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori"
)->fetchAll();

$supplier = $db->query(
    "SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier"
)->fetchAll();

// Memproses form ketika tombol Simpan ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = trim($_POST['nama_produk'] ?? '');
    $kategori_id = $_POST['kategori_id'] ?? '';
    $supplier_id = $_POST['supplier_id'] ?? '';
    $harga = trim($_POST['harga'] ?? '');
    $stok = trim($_POST['stok'] ?? '');

    if ($nama_produk === '') {
        $errors[] = 'Nama produk wajib diisi.';
    }

    if (
        !ctype_digit((string) $kategori_id) ||
        !in_array((int) $kategori_id, array_column($kategori, 'id'))
    ) {
        $errors[] = 'Silakan pilih kategori yang valid.';
    }

    if (
        !ctype_digit((string) $supplier_id) ||
        !in_array((int) $supplier_id, array_column($supplier, 'id'))
    ) {
        $errors[] = 'Silakan pilih supplier yang valid.';
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
            $sql = "
                INSERT INTO produk
                    (nama_produk, kategori_id, supplier_id, harga, stok)
                VALUES
                    (:nama_produk, :kategori_id, :supplier_id, :harga, :stok)
            ";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                'nama_produk' => $nama_produk,
                'kategori_id' => (int) $kategori_id,
                'supplier_id' => (int) $supplier_id,
                'harga' => $harga,
                'stok' => (int) $stok
            ]);

            header('Location: index.php?status=created');
            exit;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Produk gagal disimpan. Silakan periksa data.';
        }
    }
}

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Inventaris</title>

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
            font-size: 15px;
            background: white;
        }

        input:focus, select:focus {
            outline: 2px solid #bfdbfe;
            border-color: #2563eb;
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

        .button-secondary {
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
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 600px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">

        <h1>Tambah Produk</h1>

        <p class="subtitle">
            Isi informasi produk yang ingin ditambahkan ke inventaris.
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

        <form method="POST" action="create.php">

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
                    Simpan Produk
                </button>

                <a href="index.php" class="button button-secondary">
                    Kembali
                </a>
            </div>

        </form>
    </div>
</div>

</body>
</html>
