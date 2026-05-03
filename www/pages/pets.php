<?php
$pets = [];
$q = $conn->query("
    SELECT p.pet_id, p.name, p.age, p.gender, p.description, p.status, b.breed_name, c.category_name,
           (SELECT image_path FROM pet_images WHERE pet_id = p.pet_id LIMIT 1) AS image_path
    FROM pets p
    LEFT JOIN breeds b ON b.breed_id = p.breed_id
    LEFT JOIN categories c ON c.category_id = b.category_id
    WHERE p.status = 'available'
    ORDER BY p.created_at DESC
");
if ($q) $pets = $q->fetch_all(MYSQLI_ASSOC);
?>

<main class="pets-page">
    <div class="pets-header">
        <h2>🐾 Available Pets</h2>
        <p>Find your perfect companion.</p>
    </div>

    <?php if (empty($pets)): ?>
        <div class="pets-empty">
            <span>🐾</span>
            <p>No pets are currently available for adoption.</p>
        </div>
    <?php else: ?>
        <div class="pets-grid">
            <?php foreach ($pets as $pet): ?>
                <div class="pet-card" data-id="<?= $pet['pet_id'] ?>">

                    <!-- ############ Image ############ -->
                    <div class="pet-card__img">
                        <?php if ($pet['image_path']): ?>
                            <img src="<?= htmlspecialchars($pet['image_path']) ?>"
                                 alt="<?= htmlspecialchars($pet['name']) ?>">
                        <?php else: ?>
                            <div class="pet-card__img-placeholder">🐾</div>
                        <?php endif; ?>
                        <span class="pet-card__gender-badge">
                            <?= $pet['gender'] === 'male' ? '♂' : '♀' ?>
                        </span>
                    </div>

                    <!-- ############ Info ############ -->
                    <div class="pet-card__body">
                        <h3 class="pet-card__name"><?= htmlspecialchars($pet['name']) ?></h3>
                        <p class="pet-card__meta">
                            <?= htmlspecialchars($pet['breed_name'] ?? 'Unknown') ?>
                            <span class="dot">·</span>
                            <?= htmlspecialchars($pet['category_name'] ?? '') ?>
                        </p>
                        <p class="pet-card__age">
                            <?= $pet['age'] ? $pet['age'] . ' yr' . ($pet['age'] != 1 ? 's' : '') : 'Age unknown' ?>
                            <span class="dot">·</span>
                            <?= ucfirst($pet['gender'] ?? '—') ?>
                        </p>
                        <?php if ($pet['description']): ?>
                            <p class="pet-card__desc">
                                <?= htmlspecialchars(mb_strimwidth($pet['description'], 0, 80, '…')) ?>
                            </p>
                        <?php endif; ?>

                        <div class="pet-card__actions">
                            <button class="btn-view-more"
                                onclick="openPetModal(<?= htmlspecialchars(json_encode($pet)) ?>)">
                                View More 👁
                            </button>
                            <a href="index.php?page=apply&pet_id=<?= $pet['pet_id'] ?>"
                               class="btn-adopt">Adopt Me 🐾</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<!-- ############ Pet Detail Modal ############ -->
<div class="pet-modal-overlay" id="petModalOverlay" onclick="closePetModal()"></div>
<div class="pet-modal" id="petModal">
    <button class="pet-modal__close" onclick="closePetModal()">✕</button>
    <div class="pet-modal__img-wrap">
        <img id="modalImg" src="" alt="">
        <div class="pet-modal__img-placeholder" id="modalImgPlaceholder">🐾</div>
    </div>
    <div class="pet-modal__body">
        <h2 id="modalName"></h2>
        <div class="pet-modal__tags">
            <span class="modal-tag" id="modalBreed"></span>
            <span class="modal-tag" id="modalCategory"></span>
            <span class="modal-tag" id="modalGender"></span>
            <span class="modal-tag" id="modalAge"></span>
        </div>
        <p class="pet-modal__desc" id="modalDesc"></p>
        <a href="#" class="btn-adopt btn-adopt--full" id="modalAdoptBtn">Adopt Me 🐾</a>
    </div>
</div>

<link rel="stylesheet" href="../assets/css/pets_home.css">

<script src="../assets/js/pets_home.js"></script>