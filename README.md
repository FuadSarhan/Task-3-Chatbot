# 🎙️ Voice Chatbot (Gemini API)

A browser-based voice chatbot: speak into your mic, it transcribes your speech, sends the text to Gemini through a secure PHP backend, and reads the reply back out loud.

## ✨ Features

- 🎤 In-browser speech recognition (Web Speech API)
- 🔊 Text-to-speech reply (Speech Synthesis API)
- 🔐 Gemini API key stored server-side only — never exposed to the browser
- 💬 Simple, responsive chat interface

## 📁 Project Structure

```
htdocs/
├── index.html              # Main page
├── style.css                # Styling
├── app.js                    # Mic logic + backend calls
├── .htaccess                 # Blocks direct access to config.php
├── gemini-handler.php    # Receives text and calls the Gemini API
└── config.php            # Gemini API key (not committed to GitHub)
```

## 🚀 Setup

1. Get a Gemini API key from [Google AI Studio](https://aistudio.google.com/app/apikey)
2. Copy `config.example.php` to `config.php` and add your key:
   ```php
   define('GEMINI_API_KEY', 'your_key_here');
   ```
3. Upload all files to your host's `htdocs` folder (make sure all is *inside* `htdocs`, not next to it)
4. Open the site, click the mic button, and start talking 🎤

## 🐛 Issues Encountered & Fixed

### 403 Forbidden on the backend file

The backend file was originally named `chat.php`. The free host's (InfinityFree) security filter (WAF) was blocking requests to that specific filename and returning a `403 Forbidden`, even though the file's permissions and location were correct. This was confirmed by testing access under a different filename.

**Fix:** Renamed the file to `gemini-handler.php` and updated `BACKEND_URL` in `app.js` to match. After renaming, the file became accessible as expected.

## 🔒 Security Notes

- The included `.htaccess` blocks direct browser access to `config.php` even if it's accidentally uploaded to a public path.
- If the key is ever committed by mistake — even in an old commit — treat it as compromised and generate a new one immediately.

## 🛠️ Tech Stack

- HTML / CSS / JavaScript (Vanilla)
- Web Speech API (SpeechRecognition + SpeechSynthesis)
- PHP + cURL
- [Gemini API](https://ai.google.dev/) — `gemini-2.0-flash`
