<style>
        .footer-bg {
            /* Menggunakan warna biru pastel yang menyatu dengan background sebelumnya */
            background-color: #f0f6ff; 
            padding: 40px 0 30px; /* Diperpendek dari 80px ke 40px */
            color: #444;
            border-top: 1px solid rgba(0,0,0,0.05);
        }

        .footer-container {
            padding-left: 5%;
            padding-right: 5%;
        }

        .footer-logo {
            height: 50px; /* Diperkecil agar footer lebih pendek */
            margin-bottom: 15px;
            display: block;
        }

        .footer-title {
            font-weight: 700;
            margin-bottom: 15px; /* Dikurangi agar lebih rapat */
            font-size: 1.1rem; /* Ukuran font disesuaikan */
            color: #2c3e50;
        }

        .footer-list {
            list-style: none;
            padding: 0;
        }

        .footer-list li {
            margin-bottom: 8px; /* Lebih rapat */
        }

        .footer-list a {
            color: #666;
            text-decoration: none;
            font-size: 0.95rem; 
            transition: 0.2s;
        }

        .footer-list a:hover {
            color: #3498db;
            padding-left: 3px;
        }

        .copyright-text {
            font-size: 0.85rem;
            color: #7f8c8d;
            line-height: 1.5;
            margin-top: 10px;
        }

        .newsletter-wrapper {
            background-color: rgba(201, 206, 214, 0.6); /* Lebih menyatu (transparan) */
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 5px 15px;
            width: 100%;
            max-width: 320px; 
        }

        .newsletter-input {
            background: transparent;
            border: none;
            padding: 8px 0;
            font-size: 0.9rem;
            color: #333;
            width: 100%;
        }

        .newsletter-input:focus {
            outline: none;
        }

        .newsletter-btn {
            background: transparent;
            border: none;
            color: #2c3e50;
            padding-left: 10px;
            font-size: 1rem;
        }

        .social-circle {
            width: 35px;
            height: 35px;
            background-color: rgba(226, 232, 240, 0.8);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #444;
            margin-right: 8px;
            text-decoration: none;
            font-size: 1rem;
            transition: 0.3s;
        }

        .social-circle:hover {
            background-color: #3498db;
            color: white;
            transform: translateY(-2px);
        }
    </style>

    <footer class="footer-bg mt-auto">
        <div class="container-fluid footer-container">
            <div class="row justify-content-between"> 
                
                <div class="col-lg-3 mb-4 mb-lg-0">
                    <img src="assets/img/Logo2.png" alt="Logo Artavista" class="footer-logo">
                    <p class="copyright-text">
                        Copyright © 2025 ByteForge Tech<br>
                        All rights reserved
                    </p>
                    <div class="d-flex mt-3">
                        <a href="#" class="social-circle"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="social-circle"><i class="bi bi-globe"></i></a>
                        <a href="#" class="social-circle"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="social-circle"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <h6 class="footer-title">Company</h6>
                    <ul class="footer-list">
                        <li><a href="#">About us</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>

                <div class="col-6 col-md-2">
                    <h6 class="footer-title">Support</h6>
                    <ul class="footer-list">
                        <li><a href="#">Help center</a></li>
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Privacy</a></li>
                    </ul>
                </div>

                <div class="col-lg-3">
                    <h6 class="footer-title">Stay up to date</h6>
                    <div class="newsletter-wrapper mt-2">
                        <input type="email" class="newsletter-input" placeholder="Email address">
                        <button class="newsletter-btn">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>
                </div>
                
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>