<?php
include_once 'config/database.php';

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT * FROM submissions ORDER BY submitted_at DESC";
$stmt = $conn->prepare($query);
$stmt->execute();
$submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas - PraktikumSubmit</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #0078d4; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #0078d4; color: white; }
        tr:hover { background: #f5f5f5; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; }
        .submitted { background: #d4edda; color: #155724; }
        .back { display: inline-block; margin-top: 20px; color: #0078d4; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Daftar Pengumpulan Tugas</h1>
        
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>NIM</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Mata Kuliah</th>
                    <th>Status</th>
                    <th>Tanggal Submit</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                foreach ($submissions as $submission): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($submission['nim']); ?></td>
                    <td><?php echo htmlspecialchars($submission['name']); ?></td>
                    <td><?php echo htmlspecialchars($submission['class']); ?></td>
                    <td><?php echo htmlspecialchars($submission['course']); ?></td>
                    <td><span class="badge submitted"><?php echo htmlspecialchars($submission['status']); ?></span></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($submission['submitted_at'])); ?></td>
                    <td>
                        <a href="<?php echo htmlspecialchars($submission['file_url']); ?>" target="_blank" style="color: #0078d4;">📥 Download</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <a href="index.php" class="back">← Kembali ke Beranda</a>
    </div>
</body>
</html>