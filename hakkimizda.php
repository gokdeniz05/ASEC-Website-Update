<!DOCTYPE html>
<html lang="tr">
<head>
    <?php include 'includes/head-meta.php'; ?>
    <title>Hakkımızda - ASEC Kulübü</title>
    <link rel="stylesheet" href="css/hakkimizda.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <main>
        <section class="about-header">
            <h2>Hakkımızda</h2>
            <p>
                ASEC Kulübü, Ankara Yıldırım Beyazıt Üniversitesi Yazılım Mühendisliği öğrencileri tarafından kurulan, proje geliştirme, etkinlik organizasyonu, kariyer fırsatları ve uluslararası deneyimler sunarak üyelerinin mesleki gelişimini destekleyen bir topluluktur.
            </p>
        </section>
        
        <section class="about-hero">
            <div class="hero-content">
                <div class="hero-text">
                    <h2 class="animate-fade-in">Hakkımızda</h2>
                    <p class="animate-slide-up">Ankara Yıldırım Beyazıt Üniversitesi Yazılım Mühendisliği Kulübü (ASEC), 7 aktif departmanı ve 50+ üyesiyle öğrencilerin mesleki gelişimlerini destekleyen, proje geliştirme, etkinlik organizasyonu, staj ve iş fırsatları, yurtdışı deneyimleri, teknik geziler ve sponsorluk çalışmaları yürüten dinamik bir öğrenci toplululuğudur.</p>
                    <div class="hero-stats animate-scale-up">
                        <div class="stat-item">
                            <span class="stat-number">7</span>
                            <span class="stat-label">Aktif Departman</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">50+</span>
                            <span class="stat-label">Üye</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number">20+</span>
                            <span class="stat-label">Proje</span>
                        </div>
                    </div>
                </div>
                <!-- Carousel Başlangıç -->
                <div class="hero-carousel animate-fade-in">
                    <div class="carousel-slide">
                        <img src="images/gallery/galeri_6821cd1f764311.33979100.jpg" alt="Galeri 1">
                    </div>
                    <div class="carousel-slide">
                        <img src="images/gallery/galeri_6821cd1f749576.68545737.jpg" alt="Galeri 2">
                    </div>
                    <div class="carousel-slide">
                        <img src="images/gallery/gameEvent.png" alt="Galeri 3">
                    </div>
                </div>
        <!-- Carousel Bitiş -->
            </div>
        </section>
        
        <section class="vision-mission-section">
            <div class="container">
                <div class="section-header animate-fade-in">
                    <h2>Vizyonumuz & Misyonumuz</h2>
                    <div class="divider"></div>
                </div>
                <div class="vision-mission-grid">
                    <div class="vision-card animate-slide-up">
                        <div class="icon-container">
                            <!-- Lottie animasyonu -->
                            <lottie-player src="images/animations/vision.json" background="transparent" speed="1" loop autoplay></lottie-player>
                        </div>
                        <h3>Vizyonumuz</h3>
                        <p>Teknoloji dünyasında öncü, yenilikçi ve etik değerlere bağlı bireyler yetiştirerek, üyelerimizin mesleki gelişimlerini desteklemek ve sektöre daha donanımlı bireyler olarak adım atmalarını sağlamak.</p>
                    </div>
                    <div class="mission-card animate-slide-up">
                        <div class="icon-container">
                            <!-- Lottie animasyonu -->
                    <lottie-player src="images/animations/mission.json" background="transparent" speed="1" loop autoplay></lottie-player>
                        </div>
                        <h3>Misyonumuz</h3>
                        <p>Öğrencilere proje geliştirme, etkinlik organizasyonu, staj ve iş fırsatları, yurtdışı deneyimleri, teknik geziler ve sektör bağlantıları sunarak, mezun olmadan önce gerçek dünya deneyimi kazanmalarını sağlamak.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript/script.js"></script>
    <!-- Lottie Player Script -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <script>
        const slides = document.querySelectorAll('.carousel-slide');
        let currentIndex = 0;

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
        }

        // İlk slide'ı göster
        showSlide(currentIndex);

        // Otomatik değişim her 3 saniye
        setInterval(() => {
            currentIndex = (currentIndex + 1) % slides.length;
            showSlide(currentIndex);
        }, 3000);
    </script>
</body>
</html>
