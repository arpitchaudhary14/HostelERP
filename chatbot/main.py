from flask import Flask, request, jsonify
from flask_cors import CORS
from core.router import route_query
import db.db_queries as db
import os

app = Flask(__name__)
CORS(app)

# Initialize database tables on startup
db.create_chatbot_logs_table()
db.create_knowledge_table()

# ============================================================================
# Health Check Endpoint (Non-invasive)
# ============================================================================
# GET /health
# Returns 200 OK if Flask is running and database is accessible
# Used by: Docker healthcheck, load balancers, orchestration systems
# Does NOT require user_id, message, or complex routing
#
@app.route("/health", methods=["GET"])
def health():
    """
    GET /health
    Returns:
      200 OK if service is healthy
      503 Service Unavailable if database is unreachable
    """
    try:
        # Lightweight database connectivity check
        conn = db.get_db_connection()
        cursor = conn.cursor()
        cursor.execute("SELECT 1")
        cursor.close()
        conn.close()
        
        return jsonify({"status": "healthy", "service": "leon-api"}), 200
    except Exception as e:
        print(f"Health check failed: {e}")
        return jsonify({"status": "unhealthy", "error": str(e)}), 503

# ============================================================================
# Chat Endpoint (Main API)
# ============================================================================
# POST /chat
# Receives user messages and returns AI-generated responses
#
@app.route("/chat", methods=["POST"])
def chat():
    """
    POST /chat
    Input JSON:
    {
      "message": "user input",
      "history": [],
      "user_id": 123,
      "conversation_id": 456 (optional)
    }
    """
    data = request.get_json()
    if not data:
        return jsonify({"reply": "Invalid request format. JSON body required."}), 400
    user_message = data.get("message", "").strip()
    user_id = data.get("user_id")
    conv_id = data.get("conversation_id")
    if not user_message:
        return jsonify({"reply": "Please send a message."}), 400
    try:
        reply = route_query(user_id, user_message)
        return jsonify({"reply": reply})
    except Exception as e:
        print("Error during routing:", e)
        return jsonify({"reply": "An internal system error occurred."}), 500

if __name__ == "__main__":
    print("Starting LEON AI Chatbot Backend...")
    # Use environment variables for Flask configuration
    debug_mode = os.getenv("FLASK_DEBUG", "false").lower() == "true"
    app.run(host="0.0.0.0", port=5000, debug=debug_mode)
