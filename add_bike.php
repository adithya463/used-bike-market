<?php
session_start();
include 'db connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST['title']);
    $desc = htmlspecialchars($_POST['description']);
    $price = floatval($_POST['price']);
    $image = $_FILES['image']['name'];
    
    // New fields
    $year = !empty($_POST['year']) ? intval($_POST['year']) : null;
    $mileage = !empty($_POST['mileage']) ? intval($_POST['mileage']) : null;
    $engine_capacity = !empty($_POST['engine_capacity']) ? htmlspecialchars($_POST['engine_capacity']) : null;
    $fuel_type = !empty($_POST['fuel_type']) ? htmlspecialchars($_POST['fuel_type']) : null;
    $transmission = !empty($_POST['transmission']) ? htmlspecialchars($_POST['transmission']) : null;
    $color = !empty($_POST['color']) ? htmlspecialchars($_POST['color']) : null;
    $location = !empty($_POST['location']) ? htmlspecialchars($_POST['location']) : null;

    $target = "uploads/" . basename($image);

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $uid = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO bikes (user_id, title, description, price, image, year, mileage, engine_capacity, fuel_type, transmission, color, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issdsiisssss", $uid, $title, $desc, $price, $image, $year, $mileage, $engine_capacity, $fuel_type, $transmission, $color, $location);

        if ($stmt->execute()) {
            $success = "✅ Bike added successfully!";
        } else {
            $error = "❌ Database error: " . $conn->error;
        }
        $stmt->close();
    } else if ($_FILES['image']['error'] > 0) {
        $error = "❌ Upload failed: " . $_FILES['image']['error'];
    } else {
        $error = "❌ Unknown error while uploading image.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Bike</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #121212;
            --darker-bg: #1e1e1e;
            --card-bg: #2d2d2d;
            --primary: #bb86fc;
            --primary-variant: #3700b3;
            --secondary: #03dac6;
            --text: #e1e1e1;
            --text-secondary: #a0a0a0;
            --error: #cf6679;
            --success: #4caf50;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 900px; /* Wider container for landscape */
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            display: flex; /* Flex layout for landscape */
            gap: 30px; /* Space between form and preview */
        }

        .form-section {
            flex: 1;
        }

        .preview-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        input[type="file"],
        select {
            width: 100%;
            padding: 12px 15px;
            background-color: var(--darker-bg);
            border: 1px solid #444;
            border-radius: 6px;
            color: var(--text);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(187, 134, 252, 0.2);
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
            cursor: pointer;
        }

        .file-input-label {
            display: block;
            padding: 12px 15px;
            background-color: var(--darker-bg);
            border: 1px dashed #444;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            border-color: var(--primary);
            background-color: rgba(187, 134, 252, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: var(--primary);
            color: #000;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--primary-variant);
            transform: translateY(-2px);
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }

        .success {
            background-color: rgba(76, 175, 80, 0.2);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .error {
            background-color: rgba(207, 102, 121, 0.2);
            color: var(--error);
            border: 1px solid var(--error);
        }

        .preview-container {
            width: 100%;
            height: 300px;
            background-color: var(--darker-bg);
            border-radius: 8px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px dashed #444;
        }

        .image-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: none;
        }

        .preview-placeholder {
            color: var(--text-secondary);
            text-align: center;
            padding: 20px;
        }

        .preview-details {
            background-color: var(--darker-bg);
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }

        .preview-title {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: var(--primary);
        }

        .preview-description {
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-size: 0.9rem;
        }

        .preview-price {
            font-weight: bold;
            color: var(--secondary);
            font-size: 1.2rem;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                max-width: 500px;
                padding: 20px;
            }
            
            .preview-section {
                margin-top: 30px;
            }

            .form-row {
                flex-direction: column;
            }

            .form-row .form-group {
                width: 100%;
            }

            .form-section h1 {
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }

            .form-group label {
                font-size: 0.9rem;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 15px;
                margin: 10px;
            }

            .form-section h1 {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .form-group label {
                font-size: 0.85rem;
                margin-bottom: 5px;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 8px 10px;
                font-size: 13px;
            }

            .btn {
                padding: 10px 16px;
                font-size: 13px;
            }

            .preview-container {
                height: 200px;
            }

            .preview-details {
                padding: 15px;
            }

            .preview-title {
                font-size: 1rem;
            }

            .preview-price {
                font-size: 1rem;
            }
        }

        @media (max-width: 360px) {
            .container {
                padding: 10px;
                margin: 5px;
            }

            .form-section h1 {
                font-size: 1.3rem;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 6px 8px;
                font-size: 12px;
            }

            .btn {
                padding: 8px 12px;
                font-size: 12px;
            }

            .preview-container {
                height: 150px;
            }

            .preview-details {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-section">
            <h1>List Your Bike</h1>
            
            <?php if (isset($success)): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Bike Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Mountain Bike Pro 2023" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Describe your bike..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (₹)</label>
                    <input type="number" id="price" name="price" placeholder="Enter price" min="0" step="1" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="number" id="year" name="year" placeholder="e.g. 2020" min="1900" max="2024">
                    </div>
                    
                    <div class="form-group">
                        <label for="mileage">Mileage (km)</label>
                        <input type="number" id="mileage" name="mileage" placeholder="e.g. 15000" min="0">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="engine_capacity">Engine Capacity</label>
                        <input type="text" id="engine_capacity" name="engine_capacity" placeholder="e.g. 150cc">
                    </div>
                    
                    <div class="form-group">
                        <label for="color">Color</label>
                        <input type="text" id="color" name="color" placeholder="e.g. Red">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="fuel_type">Fuel Type</label>
                        <select id="fuel_type" name="fuel_type">
                            <option value="">Select Fuel Type</option>
                            <option value="Petrol">Petrol</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="transmission">Transmission</label>
                        <select id="transmission" name="transmission">
                            <option value="">Select Transmission</option>
                            <option value="Manual">Manual</option>
                            <option value="Automatic">Automatic</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" placeholder="e.g. Mumbai, Maharashtra">
                </div>
                
                <div class="form-group">
                    <label for="image">Bike Image</label>
                    <div class="file-input-wrapper">
                        <label class="file-input-label" for="image">Choose an image file</label>
                        <input type="file" id="image" name="image" accept="image/*" required>
                    </div>
                </div>
                
                <button type="submit" class="btn">List My Bike</button>
            </form>
        </div>

        <div class="preview-section">
            <div class="preview-container">
                <img id="imagePreview" class="image-preview" alt="Image preview">
                <div class="preview-placeholder" id="previewPlaceholder">
                    Image preview will appear here
                </div>
            </div>
            
            <div class="preview-details">
                <div class="preview-title" id="previewTitle">Your Bike Title</div>
                <div class="preview-description" id="previewDescription">Description will appear here</div>
                <div class="preview-price" id="previewPrice">₹0</div>
            </div>
        </div>
    </div>

    <script>
        // Live preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('previewPlaceholder');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
                
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
                placeholder.style.display = 'block';
            }
        });

        // Live text preview
        document.getElementById('title').addEventListener('input', function(e) {
            document.getElementById('previewTitle').textContent = e.target.value || 'Your Bike Title';
        });

        document.getElementById('description').addEventListener('input', function(e) {
            document.getElementById('previewDescription').textContent = e.target.value || 'Description will appear here';
        });

        document.getElementById('price').addEventListener('input', function(e) {
            const price = e.target.value ? '₹' + parseInt(e.target.value).toLocaleString() : '₹0';
            document.getElementById('previewPrice').textContent = price;
        });
    </script>
</body>
</html>