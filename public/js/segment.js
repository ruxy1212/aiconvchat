var chats = document.querySelectorAll('.c-content');
chats.forEach(chat => {
    chat.innerHTML = String(chat.innerHTML).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    chat.innerHTML = chat.innerHTML.replace(/```\s([^`]*?)```/g, '<pre><code>$1</code></pre>');
    chat.innerHTML = chat.innerHTML.replace(/```([^`]*?)```/g, '<pre><code>$1</code></pre>');
    chat.innerHTML = chat.innerHTML.replace(/`(.*?)`/g, '<code>$1</code>');
});