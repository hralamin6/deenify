# AI Chat - Quick Start Guide

## 🚀 Get Started in 3 Steps

### Step 1: Add API Key (Optional but Recommended)

Add to your `.env` file:

```env
# For best results, use OpenRouter (access to GPT-4, Claude, etc.)
OPENROUTER_API_KEY=sk-or-v1-your-key-here

# OR use Google Gemini
GEMINI_API_KEY=your-gemini-key-here

# OR use Pollinations (FREE - no key needed!)
# Already configured and ready to use
```

**Get OpenRouter API Key:**
1. Visit: https://openrouter.ai/
2. Sign up → Go to "Keys" → Create new key
3. Add $5-10 credits (very affordable)

### Step 2: Access AI Chat

Navigate to: **`/app/ai-chat`**

Or click **"AI Chat"** in the sidebar menu.

### Step 3: Start Chatting!

1. Click **"+"** button to create new conversation
2. Type your message (Markdown supported!)
3. Press **Enter** to send
4. Get AI response instantly

## ✨ Quick Tips

### Send Messages
- **Enter** = Send message
- **Shift + Enter** = New line
- Supports full Markdown syntax

### Attach Files
- Click 📎 icon
- Upload images or documents
- Max 10MB per file

### Generate Images
- Click 🎨 icon in sidebar
- Describe your image
- Free via Pollinations!

### Configure AI
- Click ⚙️ icon in sidebar
- Choose provider & model
- Adjust creativity (temperature)
- Set custom system prompt

### Message Actions
- **Copy** 📋 - Copy message text
- **Edit** ✏️ - Edit your messages
- **Regenerate** 🔄 - Get new AI response
- **Delete** 🗑️ - Remove message

## 🎯 Use Cases

**Coding Assistant**
```
System Prompt: "You are an expert Laravel developer."
Ask: "How do I optimize this database query?"
```

**Content Writer**
```
System Prompt: "You are a creative content writer."
Ask: "Write a blog post about AI in education"
```

**Learning Tutor**
```
System Prompt: "You are a patient tutor explaining concepts simply."
Ask: "Explain quantum computing like I'm 10 years old"
```

## 💰 Pricing

| Provider | Cost | Best For |
|----------|------|----------|
| **Pollinations** | FREE | Testing, casual use |
| **OpenRouter GPT-3.5** | ~$0.002/1K tokens | Daily tasks |
| **OpenRouter GPT-4** | ~$0.03/1K tokens | Complex tasks |
| **Gemini** | Free tier available | Google ecosystem |

**Example:** 100 messages with GPT-3.5 ≈ $0.20-0.50

## 🔥 Pro Features

### Markdown Support
```markdown
**Bold** *Italic* `code`

# Heading
- List item

```javascript
// Code with syntax highlighting
console.log("Hello!");
```
```

### Multiple Conversations
- Keep separate chats for different topics
- Auto-saves conversation history
- Search through past conversations

### Export Conversations
- Click export button
- Download as TXT file
- Keep records of important chats

## ⚡ Keyboard Shortcuts

- `Enter` - Send message
- `Shift + Enter` - New line in message
- `Ctrl/Cmd + C` - Copy (when text selected)

## 🐛 Troubleshooting

**"Failed to send message"**
- Check API key in `.env`
- Verify you have credits (OpenRouter)
- Try Pollinations (free, no key needed)

**No response from AI**
- Check internet connection
- Try different AI provider
- Check browser console for errors

**Images not generating**
- Pollinations is free and always works
- Be more descriptive in prompt
- Wait a moment and retry

## 📚 Learn More

Read the full documentation: `AI_CHAT_README.md`

## 🎉 You're Ready!

Start chatting at: `/app/ai-chat`

Enjoy your AI-powered conversations! 🤖✨
