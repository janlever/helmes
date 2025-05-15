<?php
session_start();

require_once 'config/database.php';
require_once 'includes/users_functions.php';
require_once 'includes/sectors_functions.php';

populateSectorsTable($conn);

$name = '';
$selectedSectors = [];
$termsAgreed = false;
$message = '';
$errors = [];

if (isset($_SESSION['user_id'])) {
    $userData = getUserData($conn, $_SESSION['user_id']);
    if (!empty($userData)) {
        $name = $userData['name'];
        $selectedSectors = $userData['sectors'] ?? [];
        $termsAgreed = $userData['agreed_to_terms'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $selectedSectors = $_POST['sectors'] ?? [];
    $termsAgreed = isset($_POST['terms_agreed']) ? 1 : 0;
    
    $errors = validateFormData($name, $selectedSectors, $termsAgreed);
    
    if (empty($errors)) {
        $userId = saveUserData($conn, $name, $selectedSectors, $termsAgreed, $_SESSION['user_id'] ?? null);
        
        if ($userId) {
            $_SESSION['user_id'] = $userId;
            $message = "Your data has been saved successfully!";
        } else {
            $errors[] = "There was an error saving your data, try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Information Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css"> 
</head>
<body>
   <div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h2 class="mb-4 text-center">Please enter your name and pick the Sectors you are currently involved in.</h2>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="bg-light p-4 border rounded">
                <div class="mb-3">
                    <label for="name" class="form-label">Name:</label>
                    <input type="text" class="form-control form-control-lg" id="name" name="name" maxlength="100" value="<?php echo htmlspecialchars($name); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="sectors" class="form-label">Sectors:</label>
                    <select multiple class="form-select form-select-lg" size="10" id="sectors" name="sectors[]" required>
                        <?php echo renderSectorsSelect($conn, $selectedSectors); ?>
                    </select>
                    <div class="form-text">Hold down the Ctrl or Command button on your keyboard to select multiple options.</div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="terms_agreed" name="terms_agreed" value="1" <?php echo $termsAgreed ? 'checked' : ''; ?> required>
                        <label class="form-check-label" for="terms_agreed">Agree to terms</label>
                    </div>
                </div>
                
                 <div class="text-center">
                    <button type="submit" class="btn btn-primary px-5 py-2">
                        <i class="bi bi-save me-2"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>