<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PIRM — Create Account</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root { --asphalt: #24262b; --safety-orange: #e8590c; --fixed-green: #2f9e44; }
  body { font-family: 'Inter', sans-serif; background: var(--asphalt); min-height: 100vh; display: flex; align-items: center; }
  .pirm-display { font-family: 'Oswald', sans-serif; text-transform: uppercase; letter-spacing: 0.03em; }
  .pirm-auth-card { max-width: 420px; margin: 0 auto; border: none; border-radius: 14px; box-shadow: 0 16px 40px rgba(0,0,0,0.3); }
  .pirm-auth-badge { width: 52px; height: 52px; border-radius: 10px; background: var(--fixed-green); display: flex; align-items: center; justify-content: center; font-size: 24px; color: #fff; margin: 0 auto 14px; }
  .btn-pirm-primary { background: var(--fixed-green); border-color: var(--fixed-green); font-weight: 600; }
  .btn-pirm-primary:hover { background: #268a3d; border-color: #268a3d; }
  .pirm-back-link { color: #c9cbd1; font-size: 13px; text-decoration: none; }
  .pirm-back-link:hover { color: #fff; }
</style>
</head>
<body>

<div class="container py-5">
  <div class="text-center mb-3">
    <a href="index.html" class="pirm-back-link"><i class="bi bi-arrow-left me-1"></i>Back to Home</a>
  </div>
  <div class="card pirm-auth-card">
    <div class="card-body p-4 p-md-5">
      <div class="text-center mb-4">
        <div class="pirm-auth-badge"><i class="bi bi-person-plus-fill"></i></div>
        <h4 class="pirm-display mb-1">Create Citizen Account</h4>
        <p class="text-muted small mb-0">Sign up to report issues and track their progress</p>
      </div>
<?php
if(isset($_SESSION['error']))
{
?>
    <div class="alert alert-danger">
        <?php echo $_SESSION['error']; ?>
    </div>
<?php
    unset($_SESSION['error']);
}

if(isset($_SESSION['success']))
{
?>
    <div class="alert alert-success">
        <?php echo $_SESSION['success']; ?>
    </div>
<?php
    unset($_SESSION['success']);
}
?>
      <form id="pirm-register-form" action="register.php" method="post">
        <div class="mb-3">
          <label class="form-label small fw-semibold">Full Name</label>
          <input type="text" class="form-control" id="pirm-reg-name" placeholder="e.g. Priya Sharma" required name="name">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" id="pirm-reg-email" placeholder="you@example.com" required>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Password</label>
          <input type="password" name="password" class="form-control" id="pirm-reg-pass" placeholder="At least 6 characters" required minlength="6">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold">Confirm Password</label>
          <input type="password" name="confirm_password" class="form-control" id="pirm-reg-pass2" placeholder="Re-enter password" required>
        </div>
        <div id="pirm-reg-error" class="text-danger small mb-3" style="display:none;"></div>
        <button type="submit" value="Register" class="btn btn-pirm-primary text-white w-100 py-2">Create Account</button>
      </form>

      <p class="text-center small text-muted mt-4 mb-0">
        Already have an account? <a href="userlogin.php">Sign in</a>
      </p>
    </div>
  </div>
</div>

</body>
</html>
