# HostelERP
A full-stack hostel management system with an AI assistant to automate student and administrative tasks.
## Key Features
*   **Role-Based Access**: Specialized portals for Admins, Wardens, and Students.
*   **Secure Authentication**: Includes Google/Microsoft Login, 2FA (Email), and reCAPTCHA protection.
*   **Operational Tools**: Manage rooms, fees, attendance, and digital documents.
*   **Logistics Tracking**: Dedicated modules for managing visitor records and student parcels.
*   **Student Life Tools**: Real-time mess menu management, notifications, and feedback.
*   **Data Export**: Quickly export student records to CSV for reporting.
*   **Activity Logging**: Tracks all administrative actions for accountability.
*   **Interactive AI**: A built-in assistant (LEON) to help users with platform tasks and actions.
## Tech Stack
*   **Backend**: PHP 8 (Logic), MySQL (Database)
*   **Frontend**: HTML5, Vanilla CSS, JavaScript, Bootstrap 5
*   **AI Microservice**: Python 3 (Flask API)
*   **Libraries**: PHPMailer, OAuth 2.0 (Google/Microsoft), Google Gemini API
## AI Overview (LEON AI)
The project features a context-aware AI bot called **LEON**, which uses:
*   **Gemini 1.5 Flash**: To understand and respond in natural language.
*   **RAG (Retrieval-Augmented Generation)**: This allows the AI to answer questions based on the hostel's specific manual.
*   **FAISS (Vector Search)**: Used for fast similarity search over stored knowledge.
*   **Agentic Behavior**: The AI is programmed to perform actions for the user, such as filing leave requests or complaints.
## Quick Setup
1.  **Environment**: Rename `.env.example` to `.env` and add your API keys and DB credentials.
2.  **Database**: Import `hostelerp_db.sql` into your MySQL server.
3.  **Python Setup**: Navigate to the `chatbot/` folder and run `pip install -r requirements.txt`.
4.  **Run**: Start your XAMPP server (Apache/MySQL) and run `python main.py` inside the `chatbot/` directory.
---
### Admin Panel
User management with role control and account actions.
![Admin](assets/screenshots/admin_users.png)
### Warden Panel
Room allocation and hostel operations management.
![Warden](assets/screenshots/warden_rooms.png)
### Student Panel
Leave requests and status tracking.
![Student](assets/screenshots/student_leave.png)
### Mess Menu
Weekly menu updated by wardens and visible to students.
![Mess Menu](assets/screenshots/mess_menu.png)
### AI Assistant (LEON)
Context-aware chatbot with RAG-based responses and actions.
![Chatbot Response 1](assets/screenshots/chatbot_response_part1.png)
![Chatbot Response 2](assets/screenshots/chatbot_response_part2.png)
*Designed to simplify hostel operations with secure and intelligent automation.*