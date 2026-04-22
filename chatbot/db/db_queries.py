import os
import mysql.connector
from dotenv import load_dotenv
load_dotenv(os.path.join(os.path.dirname(__file__), '../../.env'))
def get_db_connection():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
        database=os.getenv("DB_NAME", "hostelerp_db")
    )
def get_user_data(user_id):
    """Fetch all user-related generic information (fees, complaints, room, attendance)"""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    data = {}
    cursor.execute("SELECT id, full_name, email, phone, role FROM users WHERE id = %s", (user_id,))
    user = cursor.fetchone()
    if not user:
        return {"error": "User not found."}
    data['user'] = user
    cursor.execute("""
        SELECT r.room_number, r.capacity, ra.allocated_at 
        FROM room_allocations ra 
        JOIN rooms r ON ra.room_id = r.id 
        WHERE ra.user_id = %s AND ra.status = 'active'
    """, (user_id,))
    data['room'] = cursor.fetchone()
    cursor.execute("SELECT amount, due_date, status, paid_on FROM fees WHERE student_id = %s", (user_id,))
    data['fees'] = cursor.fetchall()
    cursor.execute("SELECT subject, status, created_at FROM complaints WHERE student_id = %s ORDER BY created_at DESC LIMIT 3", (user_id,))
    data['complaints'] = cursor.fetchall()
    role = user.get('role', 'student')
    if role == 'warden':
        cursor.execute("SELECT from_date, to_date, reason, status FROM warden_leave_requests WHERE warden_id = %s ORDER BY created_at DESC LIMIT 3", (user_id,))
    else:
        cursor.execute("SELECT from_date, to_date, reason, status FROM leave_requests WHERE student_id = %s ORDER BY created_at DESC LIMIT 3", (user_id,))
    data['leaves'] = cursor.fetchall()
    cursor.execute("SELECT date, status, marked_by FROM attendance WHERE user_id = %s ORDER BY date DESC LIMIT 5", (user_id,))
    data['attendance'] = cursor.fetchall()
    cursor.execute("SELECT date, current_status, requested_status, status FROM attendance_corrections WHERE user_id = %s ORDER BY created_at DESC LIMIT 3", (user_id,))
    data['corrections'] = cursor.fetchall()
    cursor.close()
    conn.close()
    return data
def insert_leave(user_id, from_date, to_date, reason):
    """Insert a leave request into the database"""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        cursor.execute("SELECT role FROM users WHERE id = %s", (user_id,))
        user = cursor.fetchone()
        role = user['role'] if user else 'student'
        if role == 'warden':
            cursor.execute(
                "INSERT INTO warden_leave_requests (warden_id, from_date, to_date, reason, status) VALUES (%s, %s, %s, %s, 'Pending')",
                (user_id, from_date, to_date, reason)
            )
        else:
            cursor.execute(
                "INSERT INTO leave_requests (student_id, from_date, to_date, reason, status) VALUES (%s, %s, %s, %s, 'Pending')",
                (user_id, from_date, to_date, reason)
            )
        conn.commit()
        return {"success": True, "message": "Leave request submitted successfully."}
    except Exception as e:
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        conn.close()
def insert_complaint(user_id, subject, message):
    """Insert a complaint into the database"""
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "INSERT INTO complaints (student_id, subject, message, status) VALUES (%s, %s, %s, 'Pending')",
            (user_id, subject, message)
        )
        conn.commit()
        return {"success": True, "message": "Complaint registered successfully."}
    except Exception as e:
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        conn.close()
def insert_correction(user_id, date, current_status, requested_status, reason):
    """Insert an attendance correction request into the database"""
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "INSERT INTO attendance_corrections (user_id, date, current_status, requested_status, reason, status) VALUES (%s, %s, %s, %s, %s, 'Pending')",
            (user_id, date, current_status, requested_status, reason)
        )
        conn.commit()
        return {"success": True, "message": "Attendance correction request submitted successfully."}
    except Exception as e:
        return {"success": False, "error": str(e)}
    finally:
        cursor.close()
        conn.close()
def create_chatbot_logs_table():
    """Create the chatbot_logs and conversations tables if they do not exist"""
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS chatbot_conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) DEFAULT 'New Chat',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ''')
        cursor.execute('''
            CREATE TABLE IF NOT EXISTS chatbot_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NULL,
                conversation_id INT NULL,
                message_role VARCHAR(20) NOT NULL,
                content TEXT NOT NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (conversation_id) REFERENCES chatbot_conversations(id) ON DELETE CASCADE
            )
        ''')
        cursor.execute("SHOW COLUMNS FROM chatbot_logs LIKE 'conversation_id'")
        if not cursor.fetchone():
            cursor.execute("ALTER TABLE chatbot_logs ADD COLUMN conversation_id INT NULL")
            cursor.execute("ALTER TABLE chatbot_logs ADD FOREIGN KEY (conversation_id) REFERENCES chatbot_conversations(id) ON DELETE CASCADE")
        cursor.execute("SELECT COUNT(*) FROM chatbot_logs WHERE conversation_id IS NULL AND user_id IS NOT NULL")
        if cursor.fetchone()[0] > 0:
            cursor.execute("SELECT DISTINCT user_id FROM chatbot_logs WHERE conversation_id IS NULL AND user_id IS NOT NULL")
            uids = [row[0] for row in cursor.fetchall()]
            for uid in uids:
                cursor.execute("INSERT INTO chatbot_conversations (user_id, title) VALUES (%s, 'Legacy History')", (uid,))
                new_id = cursor.lastrowid
                cursor.execute("UPDATE chatbot_logs SET conversation_id = %s WHERE user_id = %s AND conversation_id IS NULL", (new_id, uid))
        conn.commit()
    except Exception as e:
        print("Error creating chatbot_logs table:", e)
    finally:
        cursor.close()
        conn.close()
def save_chat_message(user_id, role, content, conversation_id=None):
    """Save a chat message to DB"""
    if not user_id:
        return
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "INSERT INTO chatbot_logs (user_id, message_role, content, conversation_id) VALUES (%s, %s, %s, %s)",
            (user_id, role, content, conversation_id)
        )
        conn.commit()
    except Exception as e:
        print("Error saving chat message:", e)
    finally:
        cursor.close()
        conn.close()
def get_conversations(user_id):
    """Get all conversation sessions for a user"""
    if not user_id:
        return []
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        cursor.execute(
            "SELECT id, title, created_at FROM chatbot_conversations WHERE user_id = %s ORDER BY created_at DESC",
            (user_id,)
        )
        return cursor.fetchall()
    except Exception as e:
        print("Error getting conversations:", e)
        return []
    finally:
        cursor.close()
        conn.close()
def create_conversation(user_id, title="New Chat"):
    """Create a new conversation session"""
    if not user_id:
        return None
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute(
            "INSERT INTO chatbot_conversations (user_id, title) VALUES (%s, %s)",
            (user_id, title)
        )
        conn.commit()
        return cursor.lastrowid
    except Exception as e:
        print("Error creating conversation:", e)
        return None
    finally:
        cursor.close()
        conn.close()
def delete_conversation(conversation_id):
    """Delete a conversation and all its logs"""
    conn = get_db_connection()
    cursor = conn.cursor()
    try:
        cursor.execute("DELETE FROM chatbot_conversations WHERE id = %s", (conversation_id,))
        conn.commit()
        return True
    except Exception as e:
        print("Error deleting conversation:", e)
        return False
    finally:
        cursor.close()
        conn.close()
def get_chat_history(conversation_id):
    """Get chat history for a specific conversation session"""
    if not conversation_id:
        return []
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    try:
        cursor.execute(
            "SELECT message_role as role, content FROM chatbot_logs WHERE conversation_id = %s ORDER BY timestamp ASC",
            (conversation_id,)
        )
        return cursor.fetchall()
    except Exception as e:
        print("Error getting chat history:", e)
        return []
    finally:
        cursor.close()
        conn.close()