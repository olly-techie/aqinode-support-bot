document.addEventListener('DOMContentLoaded', () => {
    const chatMessages = document.getElementById('chat-messages');
    const userInput = document.getElementById('user-input');
    const sendButton = document.getElementById('send-button');

    const addMessage = (text, sender) => {
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message');
        messageDiv.classList.add(sender === 'user' ? 'user-message' : 'bot-message');
        messageDiv.textContent = text;
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    // CONFIGURATION: Live Render Backend URL
    const API_ENDPOINT = 'https://aqinode-support-bot.onrender.com/chat.php';

    const sendMessage = async () => {
        const message = userInput.value.trim();
        if (!message) return;

        addMessage(message, 'user');
        userInput.value = '';

        // Add loading state
        const loadingDiv = document.createElement('div');
        loadingDiv.classList.add('message', 'bot-message', 'loading');
        loadingDiv.textContent = 'Typing...';
        chatMessages.appendChild(loadingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;

        try {
            const response = await fetch(API_ENDPOINT, {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message })
            });

            const data = await response.json();
            chatMessages.removeChild(loadingDiv);

            if (data.error) {
                let errorMsg = 'Error: ' + data.error;
                if (data.details && data.details.error && data.details.error.message) {
                    errorMsg += ' - ' + data.details.error.message;
                }
                addMessage(errorMsg, 'bot');
            } else {
                addMessage(data.response, 'bot');
            }
        } catch (error) {
            chatMessages.removeChild(loadingDiv);
            addMessage('Error: Could not connect to the server.', 'bot');
            console.error('Fetch error:', error);
        }
    };

    sendButton.addEventListener('click', sendMessage);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    // Welcome message
    addMessage("Yo! I'm the AqiNode Support Assistant. How can I help you today?", 'bot');
});
