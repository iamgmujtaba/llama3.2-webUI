<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Ask LLaMa 3.2</title>
    <meta name="description" content="A multimodal web UI for interacting with the LLaMa 3.2 model using Ollama. It can process text, images, and more.">
    <meta name="keywords" content="LLaMa3.2, Ollama, AI, Multimodal, Web-UI">
    <meta name="author" content="QuantumByteStudios">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 Stylesheet -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href=".">
                <h2><b>LLaMa3.2-web-ui</b></h2>
            </a>

            <ul class="navbar-nav">
                <li class="nav-item">
                    <a target="_blank" class="btn btn-dark m-1"
                        href="https://github.com/iamgmujtaba/llama3.2-webUI">
                        <i class="fa-brands fa-github"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">

        <!-- User Input Form -->
        <form action="" method="POST" enctype="multipart/form-data" id="llamaForm">
            <div class="mb-3">
                <label for="promptInput" class="form-label">Ask LLaMa (Text or Image):</label>
                <textarea class="form-control" id="promptInput" name="prompt" placeholder="Ask something..." rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="fileInput" class="form-label">Upload an Image:</label>
                <input class="form-control" type="file" id="fileInput" name="inputFile" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <!-- Display Response -->
        <div id="responseContainer" class="mt-4">
            <h5>Response:</h5>
            <div id="responseContent">
                <?php
                function processText($prompt) {
                    // Run the prompt through the multimodal model
                    $command = 'ollama run llama3.2 "' . $prompt . '"';
                    $output = shell_exec($command);
                    return nl2br(htmlspecialchars($output));
                }

                function processImage($imagePath) {
                    // Process the image through LLaMa3.2
                    $command = 'ollama run llama3.2 --input ' . escapeshellarg($imagePath);
                    $output = shell_exec($command);
                    return nl2br(htmlspecialchars($output));
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $prompt = isset($_POST['prompt']) ? htmlspecialchars($_POST['prompt']) : '';
                    $uploadedFile = $_FILES['inputFile'];

                    // Handle text input
                    if (!empty($prompt)) {
                        echo '<b>You:</b> ' . $prompt . '<br><b>Response:</b> ' . processText($prompt);
                    }

                    // Handle file upload
                    if (!empty($uploadedFile['tmp_name'])) {
                        $targetDir = "uploads/";
                        $filePath = $targetDir . basename($uploadedFile['name']);
                        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                            echo '<br><b>Image Uploaded:</b> <img src="' . $filePath . '" width="200px"><br>';
                            echo '<b>Response:</b> ' . processImage($filePath);
                        } else {
                            echo '<br>Error uploading the image.';
                        }
                    }
                } else {
                    echo 'Ask LLaMa something or upload an image to analyze!';
                }
                ?>
            </div>
        </div>
    </div>

</body>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.1/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"></script>

</html>
