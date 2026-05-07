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

<style>
/* ############ Pets Page ############ */
.pets-page {
    max-width: 1100px;
    margin: 0 auto;
    padding: 28px 20px 48px;
}
.pets-header { margin-bottom: 24px; }
.pets-header h2 { font-size: 1.6rem; font-weight: 800; margin-bottom: 4px; }
.pets-header p  { color: var(--text-muted, #777); font-size: .95rem; }

.pets-empty {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}
.pets-empty span { font-size: 3rem; display: block; margin-bottom: 12px; }

/* ############ Grid ############ */
.pets-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 20px;
}

/* ############ Card ############ */
.pet-card {
    background: var(--card-bg, #1e2a3a);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,.12);
    transition: transform .2s, box-shadow .2s;
    display: flex;
    flex-direction: column;
}
.pet-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(0,0,0,.18);
}

.pet-card__img {
    position: relative;
    width: 100%;
    aspect-ratio: 4/3;
    background: #1a2535;
    overflow: hidden;
    flex-shrink: 0;
}
.pet-card__img img {
    width: 100%; height: 100%;
    object-fit: contain;
    display: block;
    padding: 6px;
    box-sizing: border-box;
}
.pet-card:hover .pet-card__img img { transform: none; }

.pet-card__img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 3rem; color: #555;
}

.pet-card__gender-badge {
    position: absolute;
    top: 10px; right: 10px;
    background: rgba(0,0,0,.55);
    color: #fff;
    font-size: 1rem;
    width: 28px; height: 28px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(4px);
}

.pet-card__body {
    padding: 14px 16px 16px;
    display: flex;
    flex-direction: column;
    flex: 1;
}

.pet-card__name {
    font-size: 1.1rem;
    font-weight: 800;
    margin-bottom: 4px;
    color: var(--text, #fff);
}
.pet-card__meta {
    font-size: .8rem;
    color: #f39c12;
    font-weight: 600;
    margin-bottom: 3px;
}
.pet-card__age {
    font-size: .82rem;
    color: var(--text-muted, #94a3b8);
    margin-bottom: 8px;
}
.dot { opacity: .5; margin: 0 3px; }

.pet-card__desc {
    font-size: .82rem;
    color: var(--text-muted, #94a3b8);
    line-height: 1.5;
    flex: 1;
    margin-bottom: 14px;
}

.pet-card__actions {
    display: flex;
    gap: 8px;
    margin-top: auto;
}

.btn-view-more {
    flex: 1;
    background: transparent;
    border: 1.5px solid var(--border, #2d3f55);
    color: var(--text, #e2e8f0);
    padding: 8px 10px;
    border-radius: 8px;
    font-size: .82rem;
    font-weight: 600;
    cursor: pointer;
    font-family: inherit;
    transition: border-color .2s, background .2s;
    white-space: nowrap;
}
.btn-view-more:hover {
    border-color: #f39c12;
    color: #f39c12;
    background: rgba(243,156,18,.07);
}

.btn-adopt {
    flex: 1;
    background: #3b7ff5;
    color: #fff;
    padding: 8px 10px;
    border-radius: 8px;
    text-decoration: none;
    font-size: .82rem;
    font-weight: 700;
    text-align: center;
    transition: background .2s;
    white-space: nowrap;
}
.btn-adopt:hover { background: #2563eb; }
.btn-adopt--full { display: block; text-align: center; padding: 12px; font-size: .95rem; border-radius: 10px; }

/* ############ Modal ############ */
.pet-modal-overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.6);
    z-index: 900;
    backdrop-filter: blur(3px);
}
.pet-modal-overlay.open { display: block; }

.pet-modal {
    display: none;
    position: fixed;
    top: 50%; left: 50%;
    transform: translate(-50%, -48%) scale(.96);
    width: min(480px, calc(100vw - 32px));
    max-height: 88vh;
    overflow-y: auto;
    background: var(--card-bg, #1e2a3a);
    border-radius: 16px;
    box-shadow: 0 24px 64px rgba(0,0,0,.4);
    z-index: 901;
    transition: transform .25s, opacity .25s;
    opacity: 0;
}
.pet-modal.open {
    display: block;
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
}

.pet-modal__close {
    position: absolute;
    top: 12px; right: 12px;
    background: rgba(0,0,0,.4);
    border: none;
    color: #fff;
    width: 30px; height: 30px;
    border-radius: 50%;
    font-size: .9rem;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    z-index: 2;
    transition: background .2s;
}
.pet-modal__close:hover { background: #ef4444; }

.pet-modal__img-wrap {
    width: 100%;
    aspect-ratio: 16/9;
    background: #2c3a4a;
    overflow: hidden;
    border-radius: 16px 16px 0 0;
}
.pet-modal__img-wrap img {
    width: 100%; height: 100%;
    object-fit: cover;
}
.pet-modal__img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    font-size: 4rem; color: #555;
}

.pet-modal__body { padding: 20px 22px 24px; }

.pet-modal__body h2 {
    font-size: 1.4rem;
    font-weight: 800;
    margin-bottom: 10px;
    color: var(--text, #fff);
}

.pet-modal__tags {
    display: flex; flex-wrap: wrap; gap: 7px;
    margin-bottom: 14px;
}
.modal-tag {
    background: rgba(243,156,18,.15);
    color: #f39c12;
    font-size: .78rem;
    font-weight: 700;
    padding: 4px 11px;
    border-radius: 20px;
}

.pet-modal__desc {
    font-size: .9rem;
    color: var(--text-muted, #94a3b8);
    line-height: 1.65;
    margin-bottom: 20px;
}

/* ############ Responsive ############ */
@media (max-width: 600px) {
    .pets-page { padding: 20px 14px 40px; }
    .pets-grid { grid-template-columns: 1fr; gap: 14px; }
    .pet-card__img { aspect-ratio: 4/3; }
}
</style>

<script>
function openPetModal(pet) {
    const modal = document.getElementById('petModal');
    const overlay = document.getElementById('petModalOverlay');

    document.getElementById('modalName').textContent = pet.name;
    document.getElementById('modalBreed').textContent = pet.breed_name || 'Unknown breed';
    document.getElementById('modalCategory').textContent = pet.category_name || '';
    document.getElementById('modalGender').textContent = pet.gender ? (pet.gender.charAt(0).toUpperCase() + pet.gender.slice(1)) : '—';
    document.getElementById('modalAge').textContent = pet.age ? pet.age + ' yr' + (pet.age != 1 ? 's' : '') : 'Age unknown';
    document.getElementById('modalDesc').textContent  = pet.description || 'No description available.';
    document.getElementById('modalAdoptBtn').href  = 'index.php?page=apply&pet_id=' + pet.pet_id;

    const img = document.getElementById('modalImg');
    const placeholder = document.getElementById('modalImgPlaceholder');
    if (pet.image_path) {
        img.src = pet.image_path;
        img.style.display = 'block';
        placeholder.style.display = 'none';
    } else {
        img.style.display = 'none';
        placeholder.style.display = 'flex';
    }

    overlay.classList.add('open');
    // ############ Small delay so display:block kicks in before transition ############
    requestAnimationFrame(() => requestAnimationFrame(() => modal.classList.add('open')));
    document.body.style.overflow = 'hidden';
}

function closePetModal() {
    document.getElementById('petModal').classList.remove('open');
    document.getElementById('petModalOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closePetModal(); });
</script>