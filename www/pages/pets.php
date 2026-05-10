<?php
// NOTE: APP_RUNNING and $conn are already set by index.php — do NOT redefine here.

$pets = [];
$q = $conn->query("
    SELECT p.pet_id, p.name, p.age, p.gender, p.description, p.status,
           b.breed_name, c.category_name,
           (SELECT image_path FROM pet_images WHERE pet_id = p.pet_id LIMIT 1) AS image_path
    FROM pets p
    LEFT JOIN breeds b     ON b.breed_id    = p.breed_id
    LEFT JOIN categories c ON c.category_id = b.category_id
    WHERE p.status = 'available'
    ORDER BY p.created_at DESC
");
if ($q) $pets = $q->fetch_all(MYSQLI_ASSOC);
?>

<main class="container">
    <h2 style="margin-bottom:6px;">🐾 Available Pets</h2>
    <p style="color:#777;margin-bottom:24px;">Find your perfect companion.</p>

    <?php if (empty($pets)): ?>
        <p style="text-align:center;color:#999;margin-top:40px;">No pets are currently available for adoption.</p>
    <?php else: ?>
        <div class="cards">
            <?php foreach ($pets as $pet): ?>
                <div class="card">
                    <?php if ($pet['image_path']): ?>
                        <img src="<?= htmlspecialchars($pet['image_path']) ?>"
                             alt="<?= htmlspecialchars($pet['name']) ?>"
                             style="width:100%;height:180px;object-fit:cover;border-radius:8px;margin-bottom:12px;">
                    <?php else: ?>
                        <div style="width:100%;height:120px;background:#f0f0f0;border-radius:8px;margin-bottom:12px;display:flex;align-items:center;justify-content:center;font-size:2.5rem;">🐾</div>
                    <?php endif; ?>
                    <h3 style="margin-bottom:4px;"><?= htmlspecialchars($pet['name']) ?></h3>
                    <p style="color:#999;font-size:.85rem;margin-bottom:8px;">
                        <?= htmlspecialchars($pet['breed_name'] ?? 'Unknown breed') ?>
                        &middot; <?= htmlspecialchars($pet['category_name'] ?? '') ?>
                    </p>
                    <p style="font-size:.85rem;margin-bottom:8px;">
                        <?= $pet['age'] ? $pet['age'] . ' yr' . ($pet['age'] != 1 ? 's' : '') : 'Age unknown' ?>
                        &middot; <?= ucfirst($pet['gender'] ?? '—') ?>
                    </p>
                    <?php if ($pet['description']): ?>
                        <p style="font-size:.85rem;color:#555;margin-bottom:12px;">
                            <?= htmlspecialchars(mb_strimwidth($pet['description'], 0, 100, '…')) ?>
                        </p>
                    <?php endif; ?>
                    <a href="index.php?page=apply" class="btn" style="display:inline-block;font-size:.85rem;padding:8px 16px;margin-top:0;">
                        Adopt Me 🐾
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>