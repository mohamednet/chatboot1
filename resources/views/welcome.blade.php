<!DOCTYPE html>
<html>
<head>
    <title>IPTV Support Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div>
        <h2>IPTV Support Chat</h2>
        <textarea id="userPrompt" placeholder="Type your message here..."></textarea>
        <button onclick="sendMessage()">Send</button>
    </div>
    
    <div id="responseContainer"></div>

    <script>
        function sendMessage() {
            const prompt = document.getElementById('userPrompt').value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ prompt: prompt })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('responseContainer').innerHTML = `<p>${data.message}</p>`;
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
