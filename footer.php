<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>PetLeet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        .footer-icon {
            width: 24px; 
            height: 24px; 
            margin: 0 10px;
        }
        footer {
    background-color: #F2DDD0; 
}

        footer.sticky {
            position: sticky;
            bottom: 0;
            width: 100%;
        }
        .footer-content {
            padding: 20px 0;
        }
        .footer-link {
            text-decoration: none;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <footer class="bg-light text-dark sticky" style="background-color: #F2DDD0; ">
        <div class="container footer-content">
            <div class="row text-center">
                <div class="col-12 mb-3">
                    <a href="https://www.instagram.com/vts_subotica_szabadka/" aria-label="Instagram" class="footer-link" rel="noopener noreferrer">
                        <img src="images/instagram.png" alt="Instagram" class="footer-icon">
                    </a>
                    <a href="https://www.facebook.com/vtsSu/" aria-label="Facebook" class="footer-link" rel="noopener noreferrer">
                        <img src="images/facebook.png" alt="Facebook" class="footer-icon">
                    </a>
                    <a href="https://www.vts.su.ac.rs/" aria-label="VTS" class="footer-link" rel="noopener noreferrer">
                        <img src="images/vts.png" alt="VTS" class="footer-icon">
                    </a>
                </div>
                <div class="col-12">
                    <p class="text-muted mb-0">&copy; 2024 PetLeet. Minden jog fenttartva.</p>
                </div>
            </div>
        </div>
    </footer>

    <script>
        function setFooterPosition() {
            const footer = document.querySelector('footer');
            const windowHeight = window.innerHeight;
            const bodyHeight = document.body.scrollHeight;
            const footerHeight = footer.offsetHeight;

            if (windowHeight > bodyHeight) {
                footer.style.position = 'fixed';
                footer.style.bottom = '0';
                footer.style.left = '0';
                footer.style.right = '0';
            } else {
                footer.style.position = 'static'; 
            }
        }

        window.addEventListener('load', setFooterPosition);
        window.addEventListener('resize', setFooterPosition);
    </script>
</body>
</html>
