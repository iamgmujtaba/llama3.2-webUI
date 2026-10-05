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
                const OLLAMA_HOST = 'http://127.0.0.1:11434';
                const OLLAMA_MODEL = 'llama3.2';

                function processWithLlama($prompt, $imagePath = null) {
                    $request = [
                        'model' => OLLAMA_MODEL,
                        'prompt' => $prompt,
                        'stream' => false,
                    ];

                    if ($imagePath !== null) {
                        $request['images'] = [base64_encode(file_get_contents($imagePath))];
                    }

                    $context = stream_context_create([
                        'http' => [
                            'method' => 'POST',
                            'header' => "Content-Type: application/json\r\n",
                            'content' => json_encode($request),
                            'timeout' => 120,
                            'ignore_errors' => true,
                        ],
                    ]);
                    $response = file_get_contents(OLLAMA_HOST . '/api/generate', false, $context);

                    if ($response === false) {
                        return 'Unable to connect to Ollama. Make sure it is running on ' . OLLAMA_HOST . '.';
                    }

                    $payload = json_decode($response, true);
                    if (!is_array($payload) || !empty($payload['error'])) {
                        return 'Llama 3.2 provider error: ' . ($payload['error'] ?? 'Invalid response from Ollama.');
                    }

                    return $payload['response'] ?? 'The Llama 3.2 provider returned an empty response.';
                }

                function formatResponse($response) {
                    return nl2br(htmlspecialchars($response, ENT_QUOTES, 'UTF-8'));
                }

                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $prompt = trim($_POST['prompt'] ?? '');
                    $uploadedFile = $_FILES['inputFile'] ?? [];

                    // Handle text input
                    if (!empty($prompt)) {
                        echo '<b>You:</b> ' . htmlspecialchars($prompt, ENT_QUOTES, 'UTF-8') .
                            '<br><b>Response:</b> ' . formatResponse(processWithLlama($prompt));
                    }

                    // Handle file upload
                    if (!empty($uploadedFile['tmp_name']) && is_uploaded_file($uploadedFile['tmp_name'])) {
                        $targetDir = __DIR__ . '/uploads/';
                        if (!is_dir($targetDir)) {
                            mkdir($targetDir, 0755, true);
                        }
                        $extension = strtolower(pathinfo($uploadedFile['name'] ?? '', PATHINFO_EXTENSION));
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $fileName = bin2hex(random_bytes(16)) . ($extension && in_array($extension, $allowedExtensions, true) ? '.' . $extension : '');
                        $filePath = $targetDir . $fileName;
                        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                            $imagePrompt = $prompt ?: 'Describe this image.';
                            echo '<br><b>Image Uploaded:</b><br>';
                            echo '<b>Response:</b> ' . formatResponse(processWithLlama($imagePrompt, $filePath));
                            unlink($filePath);
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
