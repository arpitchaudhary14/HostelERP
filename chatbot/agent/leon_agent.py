import json
import db.db_queries as db
from rag.rag_engine import call_gemini
def process_action(user_id, query):
    """Detect the action from Gemini and execute it via DB queries."""
    system_prompt = (
        "You are LEON, an AI assistant for a Hostel ERP system. "
        "The user wants to perform an action. Detect the action from the user's input. "
        "Strictly return plain JSON (do not encapsulate in markdown) like this: "
        '{"action": "apply_leave", "data": {"from_date": "YYYY-MM-DD", "to_date": "YYYY-MM-DD", "reason": "sick leave"}} '
        "OR "
        '{"action": "submit_complaint", "data": {"subject": "...", "message": "..."}} '
        "If you cannot detect the action, return: "
        '{"action": "unknown", "data": {}}'
    )
    raw_response = call_gemini(system_prompt, query)
    if "```json" in raw_response:
        raw_response = raw_response.replace("```json", "").replace("```", "").strip()
    try:
        action_json = json.loads(raw_response)
        action_type = action_json.get("action")
        payload = action_json.get("data", {})
        reply_message = ""
        if action_type == "apply_leave":
            from_date = payload.get("from_date")
            to_date = payload.get("to_date")
            reason = payload.get("reason")
            res = db.insert_leave(user_id, from_date, to_date, reason)
            if res.get("success"):
                reply_message = f"Done! Your leave request from {from_date} to {to_date} has been submitted to the warden."
            else:
                reply_message = f"Failed to submit leave: {res.get('error')}"
        elif action_type == "submit_complaint":
            subject = payload.get("subject")
            message = payload.get("message")
            res = db.insert_complaint(user_id, subject, message)
            if res.get("success"):
                reply_message = f"Your complaint '{subject}' has been registered successfully."
            else:
                reply_message = f"Failed to submit complaint: {res.get('error')}"
        else:
            reply_message = "I couldn't quite understand what action you want to take. Could you be more specific?"
        return reply_message
    except json.JSONDecodeError:
        print("Invalid JSON returned by Gemini:", raw_response)
        return "I encountered an error trying to process your action."