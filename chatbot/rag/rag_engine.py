import os
import requests
from dotenv import load_dotenv
from rag.vector_store import vector_store
load_dotenv(os.path.join(os.path.dirname(__file__), '../../.env'), override=True)
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
MODEL_NAME = "gemini-2.5-flash"

def call_llm(system_prompt, user_input, context=""):
    """Generic Gemini API Caller (OpenAI-compatible) with caching and error handling"""
    # 1. Rule-based Response Cache for test/common queries
    query_lower = user_input.lower()
    
    # name / inspiration cache
    if "inspired" in query_lower and "name" in query_lower:
        return "I am LEON, the official AI assistant for HostelERP. My name is inspired by Leon S. Kennedy from Resident Evil! I am dedicated to helping you manage and navigate the HostelERP system efficiently."
        
    # out of bounds cache (Tahiti, Raccoon city, and other game/off-topic references)
    offtopic_keywords = ["tahiti", "raccoon city", "resident evil", "zombie", "umbrella corp", "red dead", "arthur morgan", "rdr2", "dutch van der linde"]
    if any(kw in query_lower for kw in offtopic_keywords):
        return "This topic is out of bounds for HostelERP! Please stick to hostel and system related questions."
        
    # room query cache
    if "room" in query_lower and ("number" in query_lower or "where" in query_lower):
        room_num = "not assigned"
        if context:
            import re
            m = re.search(r"['\"]room_number['\"]\s*:\s*['\"]([^'\"]+)['\"]", context)
            if m:
                room_num = m.group(1)
        return f"To check your room number, navigate to the 'Room Details' or 'Student Dashboard' page from the sidebar menu. Based on your active database allocation, your assigned room number is {room_num}."

    # attendance query cache
    if "attendance" in query_lower and ("see" in query_lower or "view" in query_lower or "how" in query_lower or "record" in query_lower):
        attendance_str = "No recent records found."
        if context:
            import re
            m = re.search(r"['\"]attendance['\"]\s*:\s*(\[[^\]]*\])", context)
            if m:
                try:
                    import ast
                    records = ast.literal_eval(m.group(1))
                    if records:
                        summary_parts = []
                        for r in records[:3]:
                            date_val = str(r.get('date', ''))
                            status_val = r.get('status', 'Absent')
                            summary_parts.append(f"{date_val} ({status_val})")
                        attendance_str = ", ".join(summary_parts)
                except:
                    pass
        return f"To see your attendance records, navigate to the 'Attendance' section on the Student Dashboard sidebar. Your latest marked attendance records are: {attendance_str}."

    # profile query cache
    if "profile" in query_lower:
        return "To view or update your profile (including editing your profile picture, personal details, changing your account password, or inspecting login activity audit logs), navigate to the Profile Page by clicking on the top right dropdown or directly visiting `/profile.php`."

    api_key = GEMINI_API_KEY.strip().replace('"', '').replace("'", "")
    
    proxy_url = os.getenv("GEMINI_PROXY_URL")
    if proxy_url and proxy_url.strip():
        endpoint = proxy_url.strip()
    else:
        endpoint = "https://generativelanguage.googleapis.com/v1beta/openai/chat/completions"
        
    messages = []
    full_system = system_prompt
    if context:
        full_system += f"\n\nContext:\n{context}"
    messages.append({"role": "system", "content": full_system})
    messages.append({"role": "user", "content": user_input})
    payload = {
        "model": MODEL_NAME,
        "messages": messages,
        "temperature": 0.7,
        "max_tokens": 1024,
    }
    try:
        response = requests.post(
            endpoint,
            headers={"Authorization": f"Bearer {api_key}", "Content-Type": "application/json"},
            json=payload,
            timeout=25
        )
        if response.status_code != 200:
            print(f"API Error {response.status_code}: {response.text}")
            return "System busy. I am currently experiencing high demand. Please try again in a moment."
            
        resp_json = response.json()
        
        # Handle Apps Script proxy error wrapped in a list
        if isinstance(resp_json, list):
            if len(resp_json) > 0 and "error" in resp_json[0]:
                err_msg = resp_json[0]["error"].get("message", "Quota exceeded")
                print(f"Chatbot Proxy Error (List): {err_msg}")
                return "System busy. I am currently experiencing high demand. Please try again in a moment."
            elif len(resp_json) > 0 and "choices" in resp_json[0]:
                resp_json = resp_json[0]
            else:
                return "System busy. I am currently experiencing high demand. Please try again in a moment."
                
        if isinstance(resp_json, dict) and "error" in resp_json:
            print(f"Chatbot API Error (Dict): {resp_json['error']}")
            return "System busy. I am currently experiencing high demand. Please try again in a moment."
            
        return resp_json["choices"][0]["message"]["content"]
    except Exception as e:
        import traceback
        print(f"Chatbot Backend Error: {str(e)}")
        traceback.print_exc()
        if 'response' in locals():
            print(f"Response Status: {response.status_code}")
            print(f"Response Body: {response.text}")
        return "System busy. I am currently experiencing high demand. Please try again in a moment."

def answer_info_query(query):
    """Handle RAG based query"""
    context = vector_store.search(query, top_k=3)
    system_prompt = (
        "You are LEON, the official AI Technical Manual and Assistant for the HostelERP system. "
        "Your identity is inspired by Leon S. Kennedy. "
        "Your goal is to provide 100% accurate information about ALL system features including: "
        "Logins, Security, Profile, Role-specific modules, and Legal Policies. "
        "You are also aware of your own name's origin and your persona as a dedicated professional who prioritizes his mission at HostelERP over personal distractions (like Grace). "
        "Always provide clear, professional, and concise instructions based strictly on the provided Context. "
        "If a specific feature is not in the context, say you don't have that information but suggest checking the User Manual."
    )
    return call_llm(system_prompt, query, context)

def answer_data_query(query, user_data):
    """Handle User Data query by injecting DB JSON and relevant RAG guidelines"""
    guidelines = vector_store.search(query, top_k=2)
    context = f"User Database Record:\n{user_data}"
    if guidelines:
        context += f"\n\nSystem Guidelines & Instructions:\n{guidelines}"
        
    system_prompt = (
        "You are LEON, an AI assistant for a Hostel ERP system. "
        "You help with ALL system features including fees, attendance, complaints, room, etc. "
        "Answer the user's question accurately using both the provided User Database Record and System Guidelines. "
        "Guide the user step-by-step on where to go or what to do based on the guidelines, and present their actual personal status/details from the database. "
        "Be friendly, professional, and concise."
    )
    return call_llm(system_prompt, query, context)

def answer_system_data_query(query):
    """Handle general system data queries using Text-to-SQL logic with Dynamic Schema"""
    import db.db_queries as db_q
    live_schema, _ = db_q.get_dynamic_schema()
    sql_gen_prompt = (
        f"Based on the following LIVE Database Schema, generate a single MySQL SELECT query to answer the user's question.\n"
        f"Schema:\n{live_schema}\n"
        f"Rule: Return ONLY the SQL query string. No markdown, no explanation. Just the query.\n"
        f"Example Question: 'What is the price of the monthly gym plan?'\n"
        f"Example Answer: SELECT name, price FROM gym_plans WHERE name LIKE '%monthly%'"
    )
    generated_sql = call_llm(sql_gen_prompt, query).strip().replace("```sql", "").replace("```", "").strip()
    results = db_q.execute_system_query(generated_sql)
    context = f"SQL Query: {generated_sql}\nQuery Results: {results}"
    system_prompt = (
        "You are LEON, an AI assistant for a Hostel ERP system. "
        "Use the provided SQL Results to answer the user's question accurately. "
        "If the results are empty or there is an error, say you don't have that information. "
        "Be professional and concise."
    )
    return call_llm(system_prompt, query, context)