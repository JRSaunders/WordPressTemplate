jQuery(document).ready(function($) {
    let thread_id = localStorage.getItem('chatgpt_assistant_thread_id') || '';

    $('#send-chat').on('click', function() {
        const message = $('#chat-input').val().trim();
        if (message !== '') {
            $('#chat-input').val(''); // Clear input field
            $('#chat-display').append('<div class="user-message">' + escapeHtml(message) + '</div>');
            scrollToBottom();
        

            if (thread_id === '') {
                // Create new thread
                $.ajax({
                    url: chatgpt_assistant.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'chatgpt_assistant_create_thread',
                        _ajax_nonce: chatgpt_assistant.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            thread_id = response.data.thread_id;
                            localStorage.setItem('chatgpt_assistant_thread_id', thread_id);
                            openSSE(message);
                        } else {
                            $('#chat-display').append('<div class="assistant-response error">' + escapeHtml(response.data.message) + '</div>');
                            scrollToBottom();
                        }
                    },
                    error: function(error) {
                        console.error('Error:', error);
                        $('#chat-display').append('<div class="assistant-response error">Error with server communication.</div>');
                        scrollToBottom();
                    }
                });
            } else {
                // Use existing thread
                openSSE(message);
            }
        }
    });

    $('#reset-chat').on('click', function() {
        thread_id = '';
        localStorage.removeItem('chatgpt_assistant_thread_id');
        $('#chat-display').empty();
    });

    function openSSE(message) {
        const assistantId = $('#assistant-chat-box').data('assistant-id');
        console.log('Using Assistant ID:', assistantId); // Debug log
        
        const eventSource = new EventSource(chatgpt_assistant.ajax_url +
            '?action=chatgpt_assistant_stream' +
            '&thread_id=' + encodeURIComponent(thread_id) +
            '&message=' + encodeURIComponent(message) +
            '&assistant_id=' + encodeURIComponent(assistantId) +
            '&_ajax_nonce=' + chatgpt_assistant.nonce
        );

        const typingIndicator = $('<div>').addClass('assistant-response typing-indicator').text('Thinking...');
        $('#chat-display').append(typingIndicator);
        scrollToBottom();

        let responseDiv = null;
        let fullResponse = '';
        let isCompleted = false;

        eventSource.addEventListener('message', function(event) {
            const data = JSON.parse(event.data);
            if (data.content) {
                if (!responseDiv) {
                    typingIndicator.remove();
                    responseDiv = $('<div>').addClass('assistant-response');
                    $('#chat-display').append(responseDiv);
                }
                fullResponse += data.content;
                responseDiv.html(marked.parse(fullResponse));
                scrollToBottom();
            }
        });

        eventSource.addEventListener('completed', function(event) {
            isCompleted = true;
            typingIndicator.remove();
            eventSource.close();
            $('#reset-chat').show();
            scrollToBottom();
            $('#chat-input').focus();
        });

        eventSource.addEventListener('error', function(event) {
            if (!isCompleted) {
                typingIndicator.remove();
                if (event.data) {
                    const data = JSON.parse(event.data);
                    if (data.content) {
                        if (!responseDiv) {
                            responseDiv = $('<div>').addClass('assistant-response error');
                            $('#chat-display').append(responseDiv);
                        }
                        responseDiv.html(`<em>Error: ${data.content}</em>`);
                    }
                }
                eventSource.close();
                scrollToBottom();
            }
        });

        // Handle connection errors
        eventSource.onerror = function(error) {
            if (!isCompleted) {
                typingIndicator.remove();
                if (!responseDiv) {
                    responseDiv = $('<div>').addClass('assistant-response error');
                    $('#chat-display').append(responseDiv);
                }
                responseDiv.append('<br><em>Connection closed.</em>');
                eventSource.close();
                scrollToBottom();
            }
        };
    }

    function streamMessage(element, message) {
        let index = 0;
        const typingSpeed = 45; // Adjust typing speed here (milliseconds)

        function typeCharacter() {
            if (index < message.length) {
                element.html(escapeHtml(message.substring(0, index + 1)));
                index++;
                setTimeout(typeCharacter, typingSpeed);
            }
        }

        typeCharacter();
    }

    function scrollToBottom() {
        $('#chat-display').scrollTop($('#chat-display')[0].scrollHeight);
    }

    // Utility function to escape HTML to prevent XSS
    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Optional: Handle Enter key for sending messages
    $('#chat-input').on('keypress', function(e) {
        if (e.which === 13 && !e.shiftKey) { // Enter key without Shift
            e.preventDefault();
            $('#send-chat').click();
        }
    });

    // Add close button handler
    $('#close-chat').on('click', function(e) {
        e.stopPropagation(); // Prevent event bubbling
        $('#chat-container').slideUp(200);
    });

    // Update chat icon click handler
    $('#chat-icon').on('click', function() {
        $('#chat-container').slideToggle(200, function() {
            if ($(this).is(':visible')) {
                $('#chat-input').focus();
            }
        });
    });

    // Optional: Update click-outside handler to exclude the close button
    $(document).on('click', function(event) {
        if ($('#assistant-chat-box').hasClass('display-mode-floating-icon')) {
            const $chatBox = $('#assistant-chat-box');
            if (!$chatBox.is(event.target) && 
                $chatBox.has(event.target).length === 0) {
                $('#chat-container').slideUp(200);
            }
        }
    });
});
