```php
<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

// Mengamankan output HTML
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Notifikasi
$status = $_GET['status'] ?? '';

$messages = [
    'created'  => 'Produk berhasil ditambahkan!',
    'updated'  => 'Produk berhasil diperbarui!',
    'deleted'  => 'Produk berhasil dihapus!',
    'invalid'  => 'Permintaan tidak valid.',
    'notfound' => 'Produk tidak ditemukan.'
];

$flashMessage = $messages[$status] ?? '';

// Pencarian
$keyword = trim($_GET['search'] ?? '');
$searchKeyword = '%' . $keyword . '%';

// Pagination
$perPage = 5;
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);

if ($page === false || $page === null || $page < 1) {
    $page = 1;
}

// Menghitung jumlah produk
$countSql = "
    SELECT COUNT(*)
    FROM produk p
    INNER JOIN kategori k ON p.kategori_id = k.id
    INNER JOIN supplier s ON p.supplier_id = s.id
    WHERE p.nama_produk LIKE :keyword1
       OR k.nama_kategori LIKE :keyword2
       OR s.nama_supplier LIKE :keyword3
";

$countStmt = $db->prepare($countSql);
$countStmt->execute([
    ':keyword1' => $searchKeyword,
    ':keyword2' => $searchKeyword,
    ':keyword3' => $searchKeyword
]);

$totalProducts = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalProducts / $perPage));

if ($page > $totalPages) {
    $page = $totalPages;
}

$offset = ($page - 1) * $perPage;

// Mengambil data produk, kategori, dan supplier
$sql = "
    SELECT
        p.id,
        p.nama_produk,
        p.harga,
        p.stok,
        k.nama_kategori,
        s.nama_supplier
    FROM produk p
    INNER JOIN kategori k ON p.kategori_id = k.id
    INNER JOIN supplier s ON p.supplier_id = s.id
    WHERE p.nama_produk LIKE :keyword1
       OR k.nama_kategori LIKE :keyword2
       OR s.nama_supplier LIKE :keyword3
    ORDER BY p.id DESC
    LIMIT :batas OFFSET :mulai
";

$stmt = $db->prepare($sql);
$stmt->bindValue(':keyword1', $searchKeyword, PDO::PARAM_STR);
$stmt->bindValue(':keyword2', $searchKeyword, PDO::PARAM_STR);
$stmt->bindValue(':keyword3', $searchKeyword, PDO::PARAM_STR);
$stmt->bindValue(':batas', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':mulai', $offset, PDO::PARAM_INT);

$stmt->execute();
$products = $stmt->fetchAll();

function rupiah($angka)
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventaris Produk</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px 16px;
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.08);
        }

        h1 {
            margin-top: 0;
            color: #166534;
        }

        .description {
            color: #64748b;
            margin-bottom: 24px;
        }

        .flash-message {
            margin-bottom: 20px;
            padding: 14px 18px;
            border-radius: 8px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            font-weight: bold;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .search-box {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        input[type="text"] {
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            min-width: 230px;
            font-size: 14px;
        }

        button, .btn {
            display: inline-block;
            padding: 11px 15px;
            border: none;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-green {
            background: #15803d;
        }

        .btn-green:hover {
            background: #166534;
        }

        .btn-orange {
            background: #d97706;
        }

        .btn-red {
            background: #dc2626;
        }

        .btn-gray {
            background: #64748b;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 13px 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: middle;
        }

        th {
            background: #166534;
            color: white;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .empty {
            padding: 25px;
            text-align: center;
            color: #64748b;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .pagination a {
            padding: 9px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: #166534;
            text-decoration: none;
        }

        .pagination a.active {
            color: white;
            background: #166534;
            border-color: #166534;
        }

        .pagination a.disabled {
            color: #94a3b8;
            pointer-events: none;
            background: #f1f5f9;
        }

        .footer {
            margin-top: 24px;
            color: #64748b;
            font-size: 13px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .container {
                padding: 18px;
            }

            .toolbar {
                align-items: stretch;
            }

            .search-box {
                width: 100%;
            }

            input[type="text"] {
                min-width: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
<main class="container">

    <h1>Inventaris Produk</h1>

    <p class="description">
        Sistem Informasi Inventaris Produk menggunakan PHP, PDO, dan MySQL.
    </p>

    <?php if ($flashMessage !== ''): ?>
        <div class="flash-message">
            <?= e($flashMessage) ?>
        </div>
    <?php endif; ?>

    <div class="toolbar">
        <form action="index.php" method="GET" class="search-box">
            <input
                type="text"
                name="search"
                placeholder="Cari produk, kategori, atau supplier..."
                value="<?= e($keyword) ?>"
            >

            <button type="submit" class="btn-green">Cari</button>

            <?php if ($keyword !== ''): ?>
                <a href="index.php" class="btn btn-gray">Reset</a>
            <?php endif; ?>
        </form>

        <a href="create.php" class="btn btn-green">+ Tambah Produk</a>
    </div>

    <p>
        Jumlah produk ditemukan:
        <strong><?= $totalProducts ?></strong>
    </p>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $index => $product): ?>
                        <tr>
                            <td><?= $offset + $index + 1 ?></td>
                            <td><?= e($product['nama_produk']) ?></td>
                            <td><?= e($product['nama_kategori']) ?></td>
                            <td><?= e($product['nama_supplier']) ?></td>
                            <td><?= e(rupiah($product['harga'])) ?></td>
                            <td><?= e($product['stok']) ?></td>
                            <td>
                                <div class="actions">
                                    <a
                                        href="edit.php?id=<?= (int) $product['id'] ?>"
                                        class="btn btn-orange"
                                    >Edit</a>

                                    <a
                                        href="delete.php?id=<?= (int) $product['id'] ?>"
                                        class="btn btn-red"
                                    >Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="empty">
                            Data produk tidak ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?search=<?= urlencode($keyword) ?>&page=<?= $page - 1 ?>">
                &laquo; Sebelumnya
            </a>
        <?php else: ?>
            <a class="disabled">&laquo; Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a
                href="?search=<?= urlencode($keyword) ?>&page=<?= $i ?>"
                class="<?= $i === $page ? 'active' : '' ?>"
            ><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?search=<?= urlencode($keyword) ?>&page=<?= $page + 1 ?>">
                Berikutnya &raquo;
            </a>
        <?php else: ?>
            <a class="disabled">Berikutnya &raquo;</a>
        <?php endif; ?>
    </div>

    <div class="footer">
        Tugas Rutin 8 — CRUD Inventaris Produk
    </div>

</main>
</body>
</html>
```