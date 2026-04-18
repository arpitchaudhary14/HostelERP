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