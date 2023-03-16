var chats = document.querySelectorAll('.c-content');
chats.forEach(chat => {
    console.log(chat.innerHTML);
    
    chat.innerHTML = chat.innerHTML.replace(/```\s([^`]*?)```/g, '<pre><code>$1</code></pre>');
    chat.innerHTML = chat.innerHTML.replace(/`(.*?)`/g, '<code>$1</code>');
});