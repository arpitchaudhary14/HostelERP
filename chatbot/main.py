from flask import Flask, request, jsonify
from flask_cors import CORS
from core.router import route_query
import db.db_queries as db
app = Flask(__name__)
CORS(app)
db.create_chatbot_logs_table()
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
    app.run(host="0.0.0.0", port=5000, debug=True)