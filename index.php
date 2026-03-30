<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.html");
    exit();
}

$name = htmlspecialchars($_POST['name'] ?? 'No Name Provided');
$role = htmlspecialchars($_POST['role'] ?? '');
$phone = htmlspecialchars($_POST['phone'] ?? '');
$email = htmlspecialchars($_POST['email'] ?? '');
$social = htmlspecialchars($_POST['social'] ?? '');
$address = htmlspecialchars($_POST['address'] ?? '');

$summary = nl2br(htmlspecialchars($_POST['summary'] ?? ''));
$education = nl2br(htmlspecialchars($_POST['education'] ?? ''));
$experience = nl2br(htmlspecialchars($_POST['experience'] ?? ''));
$awards = nl2br(htmlspecialchars($_POST['awards'] ?? ''));

$skills = [];
for ($i = 1; $i <= 3; $i++) {
    $sName = htmlspecialchars($_POST["skill{$i}_name"] ?? '');
    $sPct = htmlspecialchars($_POST["skill{$i}_pct"] ?? '');
    if (!empty($sName)) {
        $skills[] = ['name' => $sName, 'pct' => $sPct];
    }
}

$uploadDir = __DIR__ . '/uploads/'; 
$uploadedImagePath = '';
$uploadErrorMessage = '';

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $cleanFileName = preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['profile_pic']['name']));
    $newFileName = uniqid() . '_' . $cleanFileName;
    $destPath = $uploadDir . $newFileName;
    if (move_uploaded_file($fileTmpPath, $destPath)) {
        $uploadedImagePath = 'uploads/' . $newFileName; 
    } else {
        $uploadErrorMessage = "ERROR: Failed to save the image.";
    }
}

$sections = [];
if (!empty($summary)) { $sections[] = '<div class="cv-card glass-panel"><h3>Profile Summary</h3><p>' . $summary . '</p></div>'; }
if (!empty($education)) { $sections[] = '<div class="cv-card glass-panel"><h3>Education</h3><p>' . $education . '</p></div>'; }
if (!empty($experience)) { $sections[] = '<div class="cv-card glass-panel"><h3>Experience</h3><p>' . $experience . '</p></div>'; }
if (!empty($skills)) {
    $skillsHtml = '<div class="cv-card glass-panel"><h3>Skills & Expertise</h3><div class="skills-wrapper">';
    foreach ($skills as $skill) {
        $skillsHtml .= '<div class="skill-row"><div class="skill-name">' . $skill['name'] . '</div><div class="skill-bar-container"><div class="skill-bar-fill" style="width: ' . $skill['pct'] . '%;"></div></div><div class="skill-pct">' . $skill['pct'] . '%</div></div>';
    }
    $skillsHtml .= '</div></div>';
    $sections[] = $skillsHtml;
}
if (!empty($awards)) { $sections[] = '<div class="cv-card glass-panel"><h3>Awards & Recognitions</h3><p>' . $awards . '</p></div>'; }

shuffle($sections);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $name; ?> - Generated CV</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="top-nav glass-panel">
        <div class="nav-logo"><i class="fas fa-layer-group"></i> CV.kioshi</div>
        <div class="nav-right">
            <div id="realtime-clock" class="clock-display"></div>
            <button id="theme-toggle" class="icon-btn" title="Toggle Light/Dark Mode">
                <i class="fas fa-sun"></i>
            </button>
        </div>
    </nav>

<div class="cv-page-wrapper">
    
    <div class="cv-actions">
        <a href="index.html"><i class="fas fa-arrow-left"></i> Edit Profile</a>
        <button onclick="window.print()"><i class="fas fa-file-pdf"></i> Save PDF</button>
    </div>

    <?php if (!empty($uploadErrorMessage)): ?>
        <div style="background-color: rgba(220, 20, 60, 0.2); color: #ffb3b3; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?php echo $uploadErrorMessage; ?>
        </div>
    <?php endif; ?>

    <div class="cv-container">
        
        <div class="cv-sidebar glass-panel">
            <div class="cv-photo-wrapper">
                <div class="photo-frame">
                    <?php if (!empty($uploadedImagePath)): ?>
                        <img src="<?php echo $uploadedImagePath; ?>" alt="Profile" class="cv-photo">
                    <?php else: ?>
                        <div class="cv-photo" style="background-color: #222; display: flex; align-items: center; justify-content: center; color: #777;">No Pic</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <h1 class="cv-name"><?php echo $name; ?></h1>
            <h2 class="cv-role"><?php echo $role; ?></h2>
            
            <div class="contact-info">
                <?php if (!empty($phone)): ?> <p><i class="fas fa-phone"></i> <?php echo $phone; ?></p> <?php endif; ?>
                <?php if (!empty($email)): ?> <p><i class="fas fa-envelope"></i> <?php echo $email; ?></p> <?php endif; ?>
                <?php if (!empty($social)): ?> <p><i class="fab fa-linkedin"></i> <?php echo $social; ?></p> <?php endif; ?>
                <?php if (!empty($address)): ?> <p><i class="fas fa-map-marker-alt"></i> <?php echo $address; ?></p> <?php endif; ?>
            </div>
        </div>

        <div class="cv-main">
            <?php 
                foreach ($sections as $section) {
                    echo $section;
                }
            ?>
        </div>
    </div>
</div>

<script>

    function updateClock() {
        const now = new Date();
        const options = { weekday: 'short', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('realtime-clock').innerText = now.toLocaleString('en-US', options);
    }
    setInterval(updateClock, 1000);
    updateClock();

    const toggleBtn = document.getElementById('theme-toggle');
    const body = document.body;

    if (localStorage.getItem('theme') === 'light') {
        body.classList.add('light-mode');
        toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
    }

    toggleBtn.addEventListener('click', () => {
        body.classList.toggle('light-mode');
        if (body.classList.contains('light-mode')) {
            localStorage.setItem('theme', 'light');
            toggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        } else {
            localStorage.setItem('theme', 'dark');
            toggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        }
    });
</script>
</body>
</html>