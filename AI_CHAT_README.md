# AI Chat Component - Setup Guide

## Overview

A professional AI chat component with support for multiple AI providers, markdown rendering, code highlighting, file uploads, and image generation.

## Features

✅ **Multiple AI Providers**
- OpenRouter (GPT-4, GPT-3.5, Claude, Llama, etc.)
- Google Gemini
- Pollinations AI (Free)

✅ **Core Features**
- Multiple conversation threads
- Message history saved to database
- Real-time streaming responses
- Message editing and regeneration
- Full message deletion

✅ **Advanced Features**
- File attachments (images, documents)
- Markdown rendering with syntax highlighting
- Copy to clipboard functionality
- Export conversations (TXT format)
- Image generation (Pollinations)
- Customizable system prompts
- Temperature and token controls

✅ **UI/UX**
- Sidebar with conversation list
- Dark mode support
- Typing indicators
- Auto-scroll to bottom
- Responsive design

## Installation

### 1. Database Migration

The migrations have already been run. Tables created:
- `ai_conversations` - Stores conversation metadata
- `ai_messages` - Stores individual messages

### 2. Environment Configuration

Add the following to your `.env` file:

```env
# OpenRouter (Recommended - Access to multiple models)
OPENROUTER_API_KEY=your_openrouter_api_key_here
OPENROUTER_MODEL=openai/gpt-3.5-turbo

# Google Gemini (Optional)
GEMINI_API_KEY=your_gemini_api_key_here

# Pollinations (Free - No API key needed)
# Used for image generation and text chat
```

### 3. Get API Keys

#### OpenRouter (Recommended)
1. Visit https://openrouter.ai/
2. Sign up for an account
3. Go to Keys section
4. Create a new API key
5. Add credits to your account (pay-as-you-go)

**Benefits:**
- Access to 100+ models (GPT-4, Claude, Llama, etc.)
- Competitive pricing
- Single API for multiple providers

#### Google Gemini (Optional)
1. Visit https://makersuite.google.com/app/apikey
2. Create an API key
3. Free tier available

#### Pollinations (Free)
- No API key required
- Free text and image generation
- Already configured

## Usage

### Access the AI Chat

Navigate to: `/app/ai-chat`

Or use the route name:
```php
route('ai-chat.index')
```

### Basic Workflow

1. **Create New Conversation**
   - Click the "+" button in sidebar
   - Or click "Start New Conversation" in main area

2. **Send Messages**
   - Type your message (Markdown supported)
   - Press Enter to send
   - Shift+Enter for new line

3. **Attach Files**
   - Click the attachment icon
   - Select files (images, documents)
   - Max 10MB per file

4. **Configure Settings**
   - Click settings icon in sidebar
   - Choose AI provider and model
   - Adjust temperature (creativity)
   - Set max tokens (response length)
   - Customize system prompt

5. **Generate Images**
   - Click the image icon in sidebar
   - Enter your image prompt
   - Image will be generated and added to conversation

6. **Message Actions**
   - **Copy**: Copy message content
   - **Edit**: Edit your messages
   - **Regenerate**: Get a new AI response
   - **Delete**: Remove messages

7. **Export Conversation**
   - Click export icon
   - Downloads as TXT file

## Markdown Support

The AI chat supports full Markdown syntax:

```markdown
# Heading 1
## Heading 2

**Bold text**
*Italic text*

- Bullet list
- Item 2

1. Numbered list
2. Item 2

`inline code`

\`\`\`javascript
// Code block with syntax highlighting
function hello() {
    console.log("Hello World!");
}
\`\`\`

[Link text](https://example.com)

> Blockquote
```

## Code Highlighting

Supported languages include:
- JavaScript, TypeScript, Python, PHP
- HTML, CSS, SQL
- Java, C++, C#, Go, Rust
- And many more...

## Available Models

### OpenRouter
- `openai/gpt-4` - Most capable
- `openai/gpt-3.5-turbo` - Fast and affordable
- `anthropic/claude-3-opus` - Excellent reasoning
- `anthropic/claude-3-sonnet` - Balanced
- `google/gemini-pro` - Google's model
- `meta-llama/llama-3-70b-instruct` - Open source
- `mistralai/mixtral-8x7b-instruct` - Fast

### Gemini
- `gemini-pro` - Text generation
- `gemini-pro-vision` - Multimodal (text + images)

### Pollinations
- `pollinations-text` - Free text generation
- `flux` - Image generation

## Customization

### System Prompts

Customize AI behavior with system prompts:

**Examples:**
- "You are a coding assistant specialized in Laravel and PHP."
- "You are a creative writer helping with storytelling."
- "You are a helpful tutor explaining complex topics simply."

### Temperature Settings

- **0.0 - 0.3**: Precise, deterministic (good for code, facts)
- **0.4 - 0.7**: Balanced (default)
- **0.8 - 2.0**: Creative, varied (good for creative writing)

### Token Limits

- **100-500**: Short responses
- **500-1000**: Medium responses (default)
- **1000-4000**: Long, detailed responses

## Troubleshooting

### "Failed to send message" Error

**Possible causes:**
1. Invalid or missing API key
2. Insufficient credits (OpenRouter)
3. Rate limit exceeded
4. Network connection issue

**Solutions:**
- Check your `.env` file has correct API keys
- Verify API key is active on provider's website
- Add credits to your OpenRouter account
- Try a different AI provider

### Images Not Generating

**Solutions:**
- Pollinations is free and doesn't require API key
- Check internet connection
- Try a more descriptive prompt
- Wait a moment and try again

### Markdown Not Rendering

**Solutions:**
- Clear browser cache
- Check browser console for JavaScript errors
- Ensure marked.js and highlight.js are loading

## File Structure

```
app/
├── Livewire/App/
│   └── AiChatComponent.php          # Main component
├── Models/
│   ├── AiConversation.php           # Conversation model
│   └── AiMessage.php                # Message model
└── Services/AI/
    ├── AiServiceInterface.php       # Interface
    ├── AiServiceFactory.php         # Factory
    ├── OpenRouterService.php        # OpenRouter integration
    ├── GeminiService.php            # Gemini integration
    └── PollinationsService.php      # Pollinations integration

resources/views/livewire/app/
├── ai-chat-component.blade.php      # Main view
└── ai-chat/
    ├── sidebar.blade.php            # Conversations sidebar
    ├── main-area.blade.php          # Chat area
    ├── message.blade.php            # Message bubble
    ├── input-area.blade.php         # Message input
    └── modals.blade.php             # Settings & image modals

config/
├── ai.php                           # AI configuration
└── services.php                     # API keys

database/migrations/
├── *_create_ai_conversations_table.php
└── *_create_ai_messages_table.php
```

## API Costs (Approximate)

### OpenRouter
- GPT-3.5 Turbo: ~$0.002 per 1K tokens
- GPT-4: ~$0.03 per 1K tokens
- Claude 3 Haiku: ~$0.00025 per 1K tokens
- Llama 3 70B: ~$0.0007 per 1K tokens

### Gemini
- Free tier: 60 requests per minute
- Paid: Very competitive pricing

### Pollinations
- **Completely FREE** (text and images)

## Security Notes

1. **Never commit API keys** to version control
2. API keys are stored in `.env` (gitignored)
3. User conversations are private (user_id scoped)
4. File uploads are validated (type, size)
5. All inputs are sanitized

## Support

For issues or questions:
1. Check this README
2. Review error logs: `storage/logs/laravel.log`
3. Check browser console for JavaScript errors
4. Verify API keys and credits

## Credits

- **OpenRouter**: https://openrouter.ai/
- **Google Gemini**: https://ai.google.dev/
- **Pollinations AI**: https://pollinations.ai/
- **Marked.js**: Markdown parser
- **Highlight.js**: Code syntax highlighting
- **Livewire**: Laravel component framework
- **TailwindCSS**: Styling
