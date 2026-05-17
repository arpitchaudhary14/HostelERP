import db.db_queries as db_q
def get_tables():
    conn = db_q.get_db_connection()
    cursor = conn.cursor()
    cursor.execute("SHOW TABLES")
    tables = cursor.fetchall()
    print("Tables in hostelerp_db:")
    for t in tables:
        print(f"- {t[0]}")
    cursor.close()
    conn.close()
if __name__ == "__main__":
    get_tables()