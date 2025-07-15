<?php
    session_start(); 
    
    include('config/db_connect.php');
    include('templates/header.php');
    $username = $password = '';

    $errors = array('username' => '', 'password' => '');

    if(isset($_POST['login'])){

        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        //check Username
        if (empty($username)) {
        $errors['username'] = 'A Username is required <br />';  
        }

        //check Password
        if (empty($password)) {
        $errors['password'] = 'A Password is required <br />';
        }
        
        if (!array_filter($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password'])) {
                    // JWT Payload
                    $payload = [
                        'u_id' => $user['u_id'],
                        'username' => $user['username'],
                        'u_role' => $user['u_role'],
                        'iat' => time(),    
                        'exp' => time() + 3600, // 1 saat geçerli
                        'jti' => bin2hex(random_bytes(8))
                    ];
                    
                    $jwt = generate_jwt($payload);
                    
                    // Token'ı HTTP-only cookie olarak kaydet
                    setcookie("jwt_token", $jwt, [
                        'expires' => time() + 3600,
                        'path' => '/',
                        'secure' => isset($_SERVER['HTTPS']), // HTTPS varsa true
                        'httponly' => true,
                        'samesite' => 'Strict'
                    ]);
                
                    $_SESSION['username'] = $username;
                    $_SESSION['u_role'] = $user['u_role'];
                    $_SESSION['u_id'] = $user['u_id'];
                    
                    header('Location: index.php');  
                    exit();
                } else {
                    $errors['password'] = 'Wrong password <br />';
                }
            } else {
                $errors['username'] = 'Username not found <br />';
            }
        $stmt->close();
        }
    }

    
?>


<!DOCTYPE html>
<html>

    

    <section class="container grey-text">
        <h4 class="center brand-text" style="font-size: 40px; margin-bottom: 24px;">Giriş Yap</h4>
        <form class="z-depth-1 form-card" method="POST">
            <div class="form-card-content">
                <label class="text" style="font-weight:600;">Username:</label>
                <input class="text" type="text" name="username" autocomplete="off" value ="<?php echo htmlspecialchars($username); ?>">
                <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['username']; ?></div>
                <label class="text" style="font-weight:600;">Password:</label>
                <input class="text" type="password" name="password" value ="<?php echo htmlspecialchars($password); ?>">
                <div class="red-text" style="margin-bottom:8px;"><?php echo $errors['password']; ?></div>
                <div class="center">
                    <input type="submit" name="login" value="Giriş Yap"  class="btn hover-effect brand z-depth-1" style="padding: 0 32px; font-weight:600;">
                </div>
            </div> 
        </form>
        <div class="center text" style="margin-top:16px;">
            Forget Password? <a href="change_password.php" class="hover-effect " style="color:var(--accent); font-weight:600;">Chane now!</a>
        </div>
        <div class="center text" style="margin-top:8px;">
            Don't have an account? <a href="sign_in.php" class="hover-effect " style="color:var(--accent); font-weight:600;">Sign in now!</a>
        </div>
    </section>



 <?php include('templates/footer.php') ?>

</html>