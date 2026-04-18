import db.db_queries as db
from rag.rag_engine import answer_info_query, answer_data_query
from agent.leon_agent import process_action
def classify_intent(query):
    """
    Implement rule-based classifier:
    - Keywords -> classify:
      - "policy", "rules", "timings", "menu" -> info
      - "my", "fees", "room", "attendance" -> data
      - "apply", "submit", "register", "request" -> action
    """
    query_lower = query.lower()
    action_keywords = [
        "apply", "submit", "register", "request", "create", 
        "post", "verify", "assign", "collect", "delete", 
        "upload", "update", "mark", "approve", "reject", "change"
    ]
    data_keywords = [
        "my", "fees", "room", "attendance", "profile", "complaints", 
        "leave status", "history", "records", "documents", "picture", "logs"
    ]
    info_keywords = [
        "policy", "rules", "timings", "menu", "curfew", "warden", 
        "who", "google", "microsoft", "login", "2fa", "captcha", 
        "otp", "privacy", "terms", "cookies", "security", "notice", 
        "visitor", "parcel", "document", "hostel", "wing", "caretaker",
        "kennedy", "grace", "leon"
    ]
    for kw in action_keywords:
        if kw in query_lower:
            return "ACTION"
    for kw in data_keywords:
        if kw in query_lower:
            return "USER DATA"
    for kw in info_keywords:
        if kw in query_lower:
            return "INFO"
    return "INFO"
def route_query(user_id, query):
    """
    Route logic based on Intent Classifier
    1. INFO -> RAG -> Gemini -> Response
    2. USER DATA -> MySQL -> Gemini -> Response
    3. ACTION -> Gemini -> AGNO Agent -> DB -> Response
    """
    intent = classify_intent(query)
    print(f"Detected Intent: {intent} for query: {query}")
    if intent == "INFO":
        return answer_info_query(query)
    elif intent == "USER DATA":
        if not user_id:
            return "I need to know who you are to check your personal data. Please log in first."
        user_data = db.get_user_data(user_id)
        return answer_data_query(query, user_data)
    elif intent == "ACTION":
        if not user_id:
            return "You need to be logged in to perform actions like submitting a leave or complaint."
        return process_action(user_id, query)
    return "I'm not exactly sure what you need. Could you rephrase your question?"