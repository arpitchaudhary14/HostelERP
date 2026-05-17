import os
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
import db.db_queries as db_q
class VectorStore:
    def __init__(self, data_path="rag/data.txt", model_name="all-MiniLM-L6-v2"):
        self.data_path = data_path
        self.model = SentenceTransformer(model_name)
        self.index = None
        self.chunks = []
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        self.full_data_path = os.path.join(base_dir, self.data_path)
        self.last_modified_file = 0
        self.last_modified_db = None
        self.create_index()
    def create_index(self):
        """Read data.txt + DB knowledge, chunk it and create FAISS index"""
        file_mtime = os.path.getmtime(self.full_data_path) if os.path.exists(self.full_data_path) else 0
        db_mtime = None
        try:
            conn = db_q.get_db_connection()
            cursor = conn.cursor()
            cursor.execute("SELECT MAX(updated_at) FROM chatbot_knowledge")
            db_mtime = cursor.fetchone()[0]
            cursor.close()
            conn.close()
        except:
            pass
        if file_mtime <= self.last_modified_file and db_mtime == self.last_modified_db and self.index is not None:
            return
        print("🔄 Syncing Knowledge Base (DB + File)...")
        all_content = ""
        if os.path.exists(self.full_data_path):
            with open(self.full_data_path, "r", encoding="utf-8") as f:
                all_content += f.read() + "\n\n"
        db_content = db_q.get_knowledge_content()
        all_content += db_content
        raw_chunks = all_content.split("\n\n")
        self.chunks = [chunk.strip() for chunk in raw_chunks if len(chunk.strip()) > 10]
        if not self.chunks:
            return
        embeddings = self.model.encode(self.chunks)
        dimension = embeddings.shape[1]
        self.index = faiss.IndexFlatL2(dimension)
        self.index.add(np.array(embeddings).astype("float32"))
        self.last_modified_file = file_mtime
        self.last_modified_db = db_mtime
    def search(self, query, top_k=2):
        """Search similar chunks (with auto-reload check)"""
        self.create_index()
        if self.index is None or not self.chunks:
            return ""
        query_vector = self.model.encode([query])
        distances, indices = self.index.search(np.array(query_vector).astype("float32"), top_k)
        results = [self.chunks[i] for i in indices[0] if i < len(self.chunks)]
        return "\n\n".join(results)
vector_store = VectorStore()