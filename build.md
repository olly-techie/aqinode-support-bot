You are a senior full-stack engineer and AI systems architect.
Build a complete AI Support Bot for AqiNode.
The bot MUST be trained on the ENTIRE website:
https://aqinode.click
NOT just the homepage — all internal pages reachable via <a href> links.
---
## 🧠 CORE REQUIREMENT (IMPORTANT)
You must implement a WEBSITE CRAWLER that:
1. Starts from https://aqinode.click
2. Follows all internal links (<a href>)
3. Collects ALL accessible pages
4. Extracts readable text from HTML
5. Stores content for retrieval (RAG system)
---
## ⚙️ ARCHITECTURE
Frontend:
- HTML + CSS + JavaScript only
- Chat UI embedded in website
Backend:
- PHP (no frameworks)
- Endpoint: /chat.php
- Endpoint: /crawl.php (optional crawler trigger)
AI:
- Groq API (llama3-70b-8192)
- Uses system prompt + retrieved context
Knowledge system:
- Crawl website pages
- Extract text
- Chunk content
- Retrieve relevant chunks per query
--
## 🧠 AI BEHAVIOR
System prompt:
- You are AqiNode Support Assistant
- Answer ONLY using provided website context
- If info is missing, say "Not found in AqiNode docs"
- Be concise and helpful
- Slight Gen-Z tone but professional
---
## 🧱 CRAWLER REQUIREMENTS
Must:
- Follow <a href> links (internal only)
- Avoid external sites
- Deduplicate URLs
- Clean HTML to text
- Store page URL + content
---
## 📦 BACKEND REQUIREMENTS
Include:
1. crawl.php
- fetches pages
- parses links
- extracts text
2. knowledge.php (or storage layer)
- stores chunks of text
3. chat.php
- receives user message
- retrieves relevant chunks
- sends to Groq API with context
---
## 🌐 FRONTEND REQUIREMENTS
- Chat UI
- Sends messages to backend
- Displays AI responses
- Shows loading state
- Mobile responsive
---
## 🔌 API FLOW
User message → backend →
retrieve relevant website chunks →
inject into prompt →
Groq API →
response →
---
## 📁 OUTPUT FORMAT
Return:
1. Full folder structure
2. Backend PHP code
3. Crawler implementation
4. Frontend UI code
5. Deployment guide (Render)
6. Environment variables
---
## 🚫 RULES
- No frameworks
- No pseudo-code
- Must be production-ready
- Must support full site crawling
- Must use only internal website data for answers
Now build the full system.