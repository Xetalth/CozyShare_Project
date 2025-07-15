<?php
    
    include('config/db_connect.php');
    
    if (isset($_SESSION['u_id'])) {
        $user_id = $_SESSION['u_id'];
        $sql = "SELECT username, profile_image FROM users WHERE u_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->bind_result($username, $profile_image);
        $stmt->fetch();
        $stmt->close();

        $name = $username ?? 'Guest';
        $profile_image = $profile_image ?? 'default-profile.png'; // Varsayılan profil resmi
    } else {
        $name = 'Guest';
        $profile_image = 'default-profile.png'; // Giriş yapılmamışsa varsayılan resim
    }

    function generate_jwt($payload, $secret = 'gizli_key', $algo = 'HS256') {
    $header = ['typ' => 'JWT', 'alg' => $algo];
    $base64UrlHeader = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
    $base64UrlPayload = rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $secret, true);
    $base64UrlSignature = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

function verify_jwt($token, $secret = 'gizli_key') {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    [$headerB64, $payloadB64, $signatureB64] = $parts;

    $signatureCheck = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $secret, true);
    $signatureCheckB64 = rtrim(strtr(base64_encode($signatureCheck), '+/', '-_'), '=');

    if (!hash_equals($signatureB64, $signatureCheckB64)) return false;

    $payload = json_decode(base64_decode(strtr($payloadB64, '-_', '+/')), true);
    if ($payload['exp'] < time()) return false;

    return $payload; // Token geçerli, payload'ı döndür
}
?>
<head>
    <title>Cozy Share</title>
    <script src="config/script.js"></script>
    <!-- head kısmına bu eklemeyi yapın -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Materialize -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <nav class="z-depth-0">
        <div class="container">
            <!-- Karanlık tema butonu sol tarafta -->
            <button id="theme-toggle" class="hover-effect btn brand z-depth-1" style="margin-right:18px;">
                <i class="fa-solid fa-moon"></i>
                <span id="theme-toggle-text" class="text" style="margin-left:4px;">Karanlık Tema</span>
            </button>
            <a href="index.php" class="brand-logo brand-text" style="margin-left:0;">Cozy Share</a>
            <ul id="nav-mobile" class="right">
                
                <li class="text">Hello <?php echo htmlspecialchars($name); ?></li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="hover-effect profile-btn">
                        <a href="profile.php" title="Profil">
                            <img src="uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profil Resmi">
                        </a>
                    </li>
                    <li><a href="add.php" class="hover-effect btn brand z-depth-1" title="Paylaşım Yap">Paylaş</a></li>
                    <li><a href="log_out.php" class="hover-effect btn brand z-depth-1" title="Çıkış">
                        <i class="fa-solid fa-power-off"></i></a>
                    </li>
                <?php else: ?>
                    <li><a href="log_in.php" class="hover-effect btn brand z-depth-1">Giriş Yap</a></li>
                <?php endif; ?>
                
            </ul>
        </div>
    </nav>
    <script>
        
        // Theme toggle logic
        const themeToggle = document.getElementById('theme-toggle');
    const themeText = document.getElementById('theme-toggle-text');
    const icon = themeToggle.querySelector('i');

    function setTheme(dark) {
        if (dark) {
            document.documentElement.classList.add('dark-theme');
            themeText.textContent = 'Light Theme';
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
            localStorage.setItem('theme', 'dark');
        } else {
            document.documentElement.classList.remove('dark-theme');
            themeText.textContent = 'Dark Theme';
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
            localStorage.setItem('theme', 'light');
        }
    }

    // Sayfa yüklendiğinde temayı ayarla
    document.addEventListener('DOMContentLoaded', function () {
        const saved = localStorage.getItem('theme');
        const isDark = saved === 'dark';
        setTheme(isDark);

        // Toggle butonuna tıklanınca temayı değiştir
        themeToggle.addEventListener('click', function () {
            const isCurrentlyDark = document.documentElement.classList.contains('dark-theme');
            setTheme(!isCurrentlyDark);
        });
    });
    

    </script>
