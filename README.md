# AqiNode AI Support Bot

A complete AI Support Bot system for AqiNode, featuring a custom website crawler, RAG-based knowledge retrieval, and a Gen-Z toned AI assistant.

## 🚀 Features
- **Custom PHP Crawler:** Crawls `https://aqinode.click`, follows internal links, and extracts text.
- **RAG System:** Keyword-based retrieval from crawled content for relevant context injection.
- **Groq API Integration:** Uses `llama3-70b-8192` for fast and intelligent responses.
- **Embedded Chat UI:** A modern, mobile-responsive frontend built with Vanilla HTML/CSS/JS.

## 📁 Project Structure
```
/backend/
  ├── crawl.php       # Website crawler
  ├── knowledge.php   # Retrieval logic
  ├── chat.php        # AI Chat endpoint
  ├── .env.example    # Environment variables template
  └── knowledge.json  # (Generated) Crawled website data
/frontend/
  ├── index.html      # Chat UI
  ├── style.css       # Chat styling
  └── script.js       # Frontend logic
```

## 🛠️ Setup & Local Development

1. **Environment Variables:**
   Copy `backend/.env.example` to `backend/.env` and add your Groq API Key.
   ```
   GROQ_API_KEY=your_actual_api_key_here
   ```

2. **Crawl the Website:**
   Run the crawler to populate the knowledge base.
   ```bash
   cd backend
   php crawl.php
   ```
   This will generate a `knowledge.json` file.

3. **Run the Backend:**
   You can use the built-in PHP server for testing from the root directory.
   ```bash
   php -S localhost:8080
   ```

4. **Access the Frontend:**
   Open `http://localhost:8080` in your browser. The root `index.php` will redirect you to the chat UI.

## 🌐 Deployment (Render)

1. **Create a Web Service:**
   Connect your GitHub repository to Render.
2. **Environment Variables:**
   Add `GROQ_API_KEY` in the Render dashboard.
3. **Build Command:**
   `php backend/crawl.php` (To crawl on every deploy)
4. **Start Command:**
   Render will automatically serve the PHP files if you use their PHP runtime.

## 🚫 Rules & Constraints
- Answer ONLY using provided website context.
- Gen-Z but professional tone.
- No external frameworks used.
