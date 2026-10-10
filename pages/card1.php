<!-- Card Section (4 card pertama tetap seperti sebelumnya) -->
<div class="container my-5">
    <div class="row justify-content-center g-4">
        <?php 
        $counter = 0;
        foreach ($wisata_favorit as $wisata) : 
            if($counter >= 4) break;
        ?>
            <div class="col-auto">
                <div class="card" style="width: 18rem;">
                    <img src="<?php echo $wisata["img"]; ?>" class="card-img-top" alt="<?php echo $wisata["title"]; ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?php echo $wisata["title"]; ?></h5>
                        <p class="card-text"><?php echo $wisata["deskripsi"]; ?></p>
                        <a href="#" class="btn btn-primary mt-auto">Lihat Selengkapnya</a>
                    </div>
                </div>
            </div>
        <?php 
            $counter++;
        endforeach; 
        ?>
    </div>
</div>

<!-- Section Destinasi Lainnya - Horizontal Slide -->
<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">🌴 Destinasi Lainnya</h2>
        <div>
            <button class="btn btn-outline-primary me-2" id="slidePrevBtn">
                <i class="bi bi-chevron-left"></i> Prev
            </button>
            <button class="btn btn-outline-primary" id="slideNextBtn">
                Next <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
    
    <div class="position-relative">
        <!-- Slide Container -->
        <div class="overflow-hidden" style="scroll-behavior: smooth;">
            <div class="d-flex flex-nowrap" id="slideContainer" style="gap: 1.5rem; overflow-x: auto; scroll-behavior: smooth; -webkit-overflow-scrolling: touch; scrollbar-width: thin;">
                <?php 
                $slideCounter = 0;
                foreach ($wisata_favorit as $wisata) : 
                    if($slideCounter < 4) {
                        $slideCounter++;
                        continue;
                    }
                ?>
                    <div class="card slide-card" style="min-width: 280px; max-width: 280px; flex-shrink: 0;">
                        <img src="<?php echo $wisata["img"]; ?>" class="card-img-top" alt="<?php echo $wisata["title"]; ?>" style="height: 180px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo $wisata["title"]; ?></h5>
                            <p class="card-text small"><?php echo substr($wisata["deskripsi"], 0, 80) . '...'; ?></p>
                            <a href="#" class="btn btn-outline-primary btn-sm mt-auto">Lihat Detail <i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Slide Indicator -->
    <div class="text-center mt-4">
        <span class="badge bg-secondary" id="slideCounter">0 / 0</span>
    </div>
</div>

<!-- JavaScript untuk kontrol slide horizontal -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const slideContainer = document.getElementById('slideContainer');
    const prevBtn = document.getElementById('slidePrevBtn');
    const nextBtn = document.getElementById('slideNextBtn');
    const slideCounterSpan = document.getElementById('slideCounter');
    
    // Hitung jumlah slide yang tersisa (setelah 4 card pertama)
    let slideItems = document.querySelectorAll('.slide-card');
    let totalSlides = slideItems.length;
    let currentIndex = 0;
    
    // Update counter
    function updateCounter() {
        if(slideCounterSpan) {
            slideCounterSpan.textContent = `${currentIndex + 1} / ${totalSlides}`;
        }
    }
    
    // Scroll ke slide berikutnya
    function nextSlide() {
        if(currentIndex < totalSlides - 1) {
            currentIndex++;
            let scrollAmount = slideItems[currentIndex].offsetLeft - slideContainer.offsetLeft;
            slideContainer.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
            updateCounter();
        }
    }
    
    // Scroll ke slide sebelumnya
    function prevSlide() {
        if(currentIndex > 0) {
            currentIndex--;
            let scrollAmount = slideItems[currentIndex].offsetLeft - slideContainer.offsetLeft;
            slideContainer.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
            updateCounter();
        }
    }
    
    // Event listener untuk tombol
    if(nextBtn) nextBtn.addEventListener('click', nextSlide);
    if(prevBtn) prevBtn.addEventListener('click', prevSlide);
    
    // Update counter saat scroll manual
    if(slideContainer) {
        slideContainer.addEventListener('scroll', function() {
            let scrollPosition = slideContainer.scrollLeft;
            for(let i = 0; i < slideItems.length; i++) {
                let itemStart = slideItems[i].offsetLeft - slideContainer.offsetLeft;
                let itemEnd = itemStart + slideItems[i].offsetWidth;
                if(scrollPosition >= itemStart - 50 && scrollPosition < itemEnd - 50) {
                    if(currentIndex !== i) {
                        currentIndex = i;
                        updateCounter();
                    }
                    break;
                }
            }
        });
    }
    
    // Inisialisasi counter
    if(totalSlides > 0) {
        updateCounter();
    } else {
        if(slideCounterSpan) slideCounterSpan.textContent = '0 / 0';
        if(prevBtn) prevBtn.disabled = true;
        if(nextBtn) nextBtn.disabled = true;
    }
});
</script>

<style>
/* Styling untuk slide card */
.slide-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.slide-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

#slideContainer {
    scrollbar-width: thin;
    scrollbar-color: #0d6efd #dee2e6;
    padding-bottom: 10px;
}

#slideContainer::-webkit-scrollbar {
    height: 8px;
}

#slideContainer::-webkit-scrollbar-track {
    background: #dee2e6;
    border-radius: 10px;
}

#slideContainer::-webkit-scrollbar-thumb {
    background: #0d6efd;
    border-radius: 10px;
}

#slideContainer::-webkit-scrollbar-thumb:hover {
    background: #0a58ca;
}

.btn-outline-primary:hover .bi-chevron-left,
.btn-outline-primary:hover .bi-chevron-right {
    transform: translateX(0);
}
</style>