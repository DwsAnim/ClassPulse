# ClassPulse

[Access the live app here!](https://classpulse.joerexsports.com/ClassPulse)

> **Live classroom engagement, AI-powered analytics, and zero student friction.**

ClassPulse is a browser-based web app that lets teachers run live quizzes, polls, and Q&As during class. Students join instantly on any phone or laptop — no app download, no account needed. After every session, an AI-powered analytics dashboard shows the teacher exactly which students are struggling, which topics need reteaching, and how the whole class is performing in real time.

---

## The Problem & Solution

| | |
|---|---|
| **Problem** | Teachers have no idea who is lost in class until exam results come back — by then it is too late to help. |
| **Solution** | ClassPulse gives teachers a live pulse of their classroom and flags struggling students automatically after every session. |
| **Platform** | Web browser only — no app download. Works on any phone, laptop, 2G/3G network. |

---

## Features

### Teacher / Lecturer
- Register and login with a personal account
- Create named classroom sessions with auto-generated 6-digit room code and QR code
- Build **MCQ, True/False, Short Answer, and Math/Formula** questions manually
- Upload lecture notes (PDF or plain text) to **auto-generate questions via AI** (Google Gemini)
- Edit, delete, or add to AI-generated questions before launching
- Set 15, 30, or 60 second countdown timer per question
- Launch questions live to all connected students simultaneously
- Live response dashboard with real-time bar chart (Chart.js)
- Confusion meter heatmap showing how students feel after each question
- Animated leaderboard reveal at end of session
- Full **AI analytics report** — weak students flagged, weak topics identified, re-teach priority list
- Download session summary as PDF
- View full history of all past sessions

### Student
- Join with a room code — **no account or password needed**
- Works on any phone or laptop browser
- Questions appear automatically without page refresh
- Math and formula questions rendered clearly with **KaTeX**
- Instant correct/wrong feedback after every answer
- Confusion meter tap after each question (Got it / Unsure / Lost)
- Emoji reactions — Too Fast, Got It, Confused
- Final personal score and animated class leaderboard reveal
- Optimised for 2G and 3G low-data connections

---

## User Flow

### Teacher
1. Register / login
2. Create a session — system auto-generates room code and QR code
3. Project the room code so students can join
4. Build questions manually or upload lecture notes for AI generation
5. Set question type and timer
6. Launch questions live — watch real-time responses roll in
7. End session — leaderboard reveals, AI report generates automatically
8. Review full analytics report

### Student
1. Open browser, go to ClassPulse URL, enter room code and name
2. Wait in lobby until teacher starts
3. Answer questions with countdown timer
4. Get instant feedback after each answer
5. Tap confusion meter
6. See final score and class leaderboard

---

## AI Features

### Question Generation from Lecture Notes
Lecturers upload a PDF or plain text file of their lecture notes. The backend extracts the text and sends it to the **Google Gemini API**, which returns a set of MCQ questions with 4 options and correct answers marked. The lecturer reviews, edits, and approves questions before launching.

| Version | Supported Input | Notes |
|---|---|---|
| v1 (Buildathon) | PDF upload and plain text paste | Most reliable, fastest to build |
| v2 (Post-Buildathon) | Camera snap of handwritten notes via OCR (Tesseract.js) | More ambitious, add after launch |

### AI Analytics Dashboard
After every session ends, the system automatically analyses all responses and generates a smart intelligence report.

**Class Overview**
- Total students, average class score, session completion rate
- Highest and lowest individual score
- Score distribution bar chart

**Student Intelligence**
- Students ranked: Excelling / Average / Needs Attention
- Individual weak areas flagged per student
- Confusion meter results per student
- Students who answered consistently slow — identified and flagged

**Question Analysis**
- Questions ranked easiest to hardest
- Most-missed questions and most-selected wrong answers
- Average time taken per question
- Heatmap — question difficulty vs class performance

**AI Weak Area Detection**
- Automatically groups students by shared knowledge gap
- Generates a Re-teach Priority List
- Suggests specific topics to revisit
- Performance trend line chart across multiple sessions

---

## Math & Formula Support

ClassPulse handles STEM questions in two ways:

**Option A — KaTeX Formula Rendering (Primary)**

| Lecturer Types | Student Sees |
|---|---|
| `x = \frac{-b \pm \sqrt{b^2 - 4ac}}{2a}` | Rendered quadratic formula |
| `\begin{pmatrix} a & b \\ c & d \end{pmatrix}` | A clean 2×2 matrix |
| `\int_{a}^{b} f(x)\,dx` | A definite integral with limits |
| `\sum_{i=1}^{n} x_i` | Sigma summation notation |

KaTeX loads via CDN — free, no signup, works on 2G, renders instantly on mobile.

**Option B — Image Upload (Backup)**
For complex diagrams or unusual notation, the lecturer uploads a photo of the equation. Displayed alongside the question text for students.

---

## Pages

| Page | Users | Purpose |
|---|---|---|
| Landing page | Everyone | Introduction, links to teacher login and student join |
| Teacher register / login | Teacher | Create account and log in |
| Teacher dashboard | Teacher | View past sessions, create new session, analytics history |
| Session creator | Teacher | Build questions, upload notes for AI generation, generate room code and QR |
| AI question review | Teacher | Review, edit, delete and approve AI-generated questions |
| Live session control | Teacher | Launch questions, see live responses, confusion meter, end session |
| Leaderboard reveal | Both | Animated top 3 reveal after session ends |
| AI analytics report | Teacher | Full post-session report with charts, weak students, re-teach list |
| Student join page | Student | Enter room code and name |
| Student lobby | Student | Waiting screen before session starts |
| Student answer page | Student | Live question with KaTeX rendering, timer, answer buttons, confusion meter |
| Student result page | Student | Final score, rank, class leaderboard |

---

## Tech Stack

| Layer | Technology | Purpose |
|---|---|---|
| Frontend | HTML5 + CSS3 | Structure and styling |
| Frontend | Bootstrap 5 | Responsive mobile-first layout |
| Frontend | Vanilla JavaScript | Polling, timers, UI updates |
| Charts | Chart.js (CDN) | Bar charts, pie charts, line charts |
| Math Formulas | KaTeX (CDN) | Render equations beautifully |
| QR Code | QRCode.js (CDN) | Auto-generate room QR code |
| PDF Export | jsPDF (CDN) | Download session summary reports |
| OCR (v2) | Tesseract.js (CDN) | Read text from uploaded note photos |
| Backend | PHP 8 | Server logic and API endpoints |
| Real-time | JS Polling (every 2s) | Live updates without page reload |
| Auth | PHP Sessions + Cookies | Secure teacher login |
| Database | MySQL | Rooms, questions, answers, scores |
| Dev Server | XAMPP | Local development environment |
| AI Generation | Google Gemini API (free tier) | Auto-generate questions from notes |
| Code Sharing | GitHub | Team collaboration |
| Hosting | Vercel + Railway (free tier) | Deploy frontend and backend |

> **AI API Note:** This project uses the Google Gemini API from [aistudio.google.com](https://aistudio.google.com) — completely free with a generous limit, more than enough for development and live demo.



---

## What Makes ClassPulse Stand Out

| Feature | Why It Matters |
|---|---|
| **AI Question Generation** | Lecturers upload their notes and the system generates quiz questions automatically. No other free classroom tool does this. |
| **Math Formula Support** | KaTeX renders quadratic formulas, matrices, integrals and more in the browser. STEM lecturers can finally run live quizzes on technical subjects. |
| **Confusion Meter** | Students rate how they feel after every question. Turns ClassPulse into a teaching intelligence tool, not just a quiz platform. |
| **AI Weak Area Detection** | Automatically groups students by knowledge gap and generates a re-teach priority list. No other free tool offers this. |
| **Zero Student Friction** | No app download. No account. Just a browser and a 6-digit code. Works on any phone, even on 2G. |
| **Built for African Schools** | Lightweight, low-data, works on 3G, no expensive subscriptions. Designed for the reality of Nigerian and African classrooms. |

---

## One-Line Pitch

> *"ClassPulse is the first live classroom engagement tool built for African schools — lecturers upload their notes and AI generates the quiz, math formulas render on any phone, and after every class the system tells the teacher exactly which students need help before the exam."*

---

*Tags: `Education` `EdTech` `AI-Powered` `Real-time` `STEM-ready` `No app needed` `Built for Africa` `100% Free Stack`*
