import os
import requests
from dotenv import load_dotenv
from rag.vector_store import vector_store
load_dotenv(os.path.join(os.path.dirname(__file__), '../../.env'))
GEMINI_API_KEY = os.getenv("GEMINI_API_KEY")
def call_gemini(system_prompt, user_input, context=""):
    """Generic Gemini API Caller matching the strict instruction format"""
    api_key = GEMINI_API_KEY.strip().replace('"', '').replace("'", "")
    endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent"
    payload = {
        "contents": [{"parts": [{"text": f"System: {system_prompt}\n\nContext:\n{context}\n\nUser: {user_input}"}]}]
    }
    try:
        response = requests.post(endpoint, params={"key": api_key}, json=payload, timeout=25)
        if response.status_code != 200:
            print(f"Gemini API Error {response.status_code}: {response.text}")
            response.raise_for_status()
        return response.json()["candidates"][0]["content"]["parts"][0]["text"]
    except Exception as e:
        print(f"Chatbot Backend Error: {str(e)}")
        return "I'm having trouble connecting to my brain right now. Please try again."
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
    return call_gemini(system_prompt, query, context)
def answer_data_query(query, user_data):
    """Handle User Data query by injecting DB JSON"""
    context = f"User Database Record:\n{user_data}"
    system_prompt = (
        "You are LEON, an AI assistant for a Hostel ERP system. "
        "You help with ALL system features including fees, attendance, complaints, room, etc. "
        "Answer the user's question accurately using ONLY the provided User Database Record. "
        "If the data is missing, say you don't have that information. Be friendly and concise."
    )
    return call_gemini(system_prompt, query, context)
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
    generated_sql = call_gemini(sql_gen_prompt, query).strip().replace("```sql", "").replace("```", "").strip()
    results = db_q.execute_system_query(generated_sql)
    context = f"SQL Query: {generated_sql}\nQuery Results: {results}"
    system_prompt = (
        "You are LEON, an AI assistant for a Hostel ERP system. "
        "Use the provided SQL Results to answer the user's question accurately. "
        "If the results are empty or there is an error, say you don't have that information. "
        "Be professional and concise."
    )
    return call_gemini(system_prompt, query, context)