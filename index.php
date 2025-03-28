<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $input = json_decode(file_get_contents("php://input"), true);
    $prompt = $input['prompt'] ?? '';

    if (empty($prompt)) {
        echo json_encode(['success' => false, 'message' => 'Prompt is required']);
        exit;
    }

    $apiKey = ''; // Replace with your actual API key
    $payload = [
        "model" => "black-forest-labs/flux-schnell",
        "response_format" => "b64_json",
        "response_extension" => "png",
        "width" => 1024,
        "height" => 1024,
        "num_inference_steps" => 4,
        "negative_prompt" => "",
        "seed" => -1,
        "prompt" => $prompt
    ];

    $ch = curl_init('https://api.studio.nebius.com/v1/images/generations');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $response = curl_exec($ch);

  //  var_dump($response); // Debugging line to check the response from the API

    if (curl_errno($ch)) {
        echo json_encode(['success' => false, 'message' => curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    curl_close($ch);
    $result = json_decode($response, true);

    if (isset($result['data'][0]['b64_json'])) {
        echo json_encode(['success' => true, 'image' => $result['data'][0]['b64_json']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to generate image']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>🎨 AI Image Generator</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #121212;
            color: #f5f5f5;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        header {
            margin-top: 40px;
            font-size: 2rem;
            font-weight: bold;
        }
        form {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        input {
            padding: 12px 20px;
            width: 300px;
            border: none;
            border-radius: 8px;
            outline: none;
            font-size: 16px;
        }
        button {
            background: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #4338ca;
        }
        #loader {
            margin-top: 20px;
            display: none;
        }
        #result {
            margin-top: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        #result img {
            max-width: 400px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>
    <header>🎨 AI Image Generator</header>
    <form id="promptForm">
        <input type="text" id="prompt" placeholder="Describe anything..." required />
        <button type="submit">Generate</button>
    </form>
    <div id="loader">⚙️ Generating Image...</div>
    <div id="result"></div>

    <script>
        const form = document.getElementById('promptForm');
        const promptInput = document.getElementById('prompt');
        const loader = document.getElementById('loader');
        const result = document.getElementById('result');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const prompt = promptInput.value.trim();
            if (!prompt) return;

            loader.style.display = 'block';
            result.innerHTML = '';

            try {
                const res = await fetch('', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ prompt })
                });
                const data = await res.json();
                if (data.success) {
                    const img = document.createElement('img');
                    img.src = `data:image/png;base64,${data.image}`;
                    result.appendChild(img);
                } else {
                    result.innerHTML = `<p style="color:red;">${data.message}</p>`;
                }
            } catch (error) {
                result.innerHTML = `<p style="color:red;">Error occurred!</p>`;
            } finally {
                loader.style.display = 'none';
            }
        });
    </script>
</body>
</html>
