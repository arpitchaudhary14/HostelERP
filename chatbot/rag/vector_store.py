import os
import faiss
import numpy as np
from sentence_transformers import SentenceTransformer
class VectorStore:
    def __init__(self, data_path="rag/data.txt", model_name="all-MiniLM-L6-v2"):
        self.data_path = data_path
        self.model = SentenceTransformer(model_name)
        self.index = None
        self.chunks = []
        base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
        self.full_data_path = os.path.join(base_dir, self.data_path)
        self.create_index()
    def create_index(self):
        """Read data.txt, chunk it and create FAISS index"""
        if not os.path.exists(self.full_data_path):
            print(f"Data file not found at {self.full_data_path}")
            return
        with open(self.full_data_path, "r", encoding="utf-8") as f:
            content = f.read()
        raw_chunks = content.split("\n\n")
        self.chunks = [chunk.strip() for chunk in raw_chunks if len(chunk.strip()) > 10]
        if not self.chunks:
            return
        embeddings = self.model.encode(self.chunks)
        dimension = embeddings.shape[1]
        self.index = faiss.IndexFlatL2(dimension)
        self.index.add(np.array(embeddings).astype("float32"))
    def search(self, query, top_k=2):
        """Search similar chunks"""
        if self.index is None or not self.chunks:
            return ""
        query_vector = self.model.encode([query])
        distances, indices = self.index.search(np.array(query_vector).astype("float32"), top_k)
        results = [self.chunks[i] for i in indices[0] if i < len(self.chunks)]
        return "\n\n".join(results)
vector_store = VectorStore()