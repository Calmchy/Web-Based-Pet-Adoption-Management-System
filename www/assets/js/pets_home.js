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